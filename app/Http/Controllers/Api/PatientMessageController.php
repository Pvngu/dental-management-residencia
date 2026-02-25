<?php

namespace App\Http\Controllers\Api;

use App\Classes\Common;
use App\Http\Controllers\ApiBaseController;
use App\Http\Requests\Api\PatientMessage\DeleteRequest;
use App\Http\Requests\Api\PatientMessage\IndexRequest;
use App\Http\Requests\Api\PatientMessage\StoreRequest;
use App\Models\Patient;
use App\Models\PatientMessage;
use App\Services\SmsService;
use App\Traits\CompanyTraits;
use Examyou\RestAPI\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PatientMessageController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = PatientMessage::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    /**
     * Get all conversations (patients with messages) for the message center
     */
    public function getConversations(Request $request)
    {
        $companyId = company()->id;
        $search = $request->input('search', '');
        $filter = $request->input('filter', 'all'); // all, unread
        $channel = $request->input('channel', null); // sms, whatsapp, or null for all
        
        // Get patients who have messages, with their latest message info
        $query = Patient::select([
                'patients.id',
                'users.name',
                'users.last_name',
                'users.phone',
                'users.profile_image',
            ])
            ->join('users', 'patients.user_id', '=', 'users.id')
            ->where('patients.company_id', $companyId)
            ->whereHas('messages')
            ->with(['messages' => function($q) {
                $q->orderBy('created_at', 'desc')->limit(1);
            }])
            ->withCount(['messages as unread_count' => function($q) use ($channel) {
                $q->where('direction', 'inbound')->whereNull('read_at');
                if ($channel) {
                    $q->where('channel', $channel);
                }
            }]);
        
        // Apply search filter
        if ($search) {
            $query->where(function($q) use ($search) {
                // Search by name, last_name, phone
                $q->where('users.name', 'like', "%{$search}%")
                  ->orWhere('users.last_name', 'like', "%{$search}%")
                  ->orWhere('users.phone', 'like', "%{$search}%")
                  // Search by full name (concatenated)
                  ->orWhere(DB::raw("CONCAT(users.name, ' ', users.last_name)"), 'like', "%{$search}%")
                  // Search by address fields
                  ->orWhereHas('user.addresses', function($addressQuery) use ($search) {
                      $addressQuery->where('address_line_1', 'like', "%{$search}%")
                                  ->orWhere('address_line_2', 'like', "%{$search}%")
                                  ->orWhere('city', 'like', "%{$search}%")
                                  ->orWhere('state', 'like', "%{$search}%")
                                  ->orWhere('neighborhood', 'like', "%{$search}%")
                                  ->orWhere('postal_code', 'like', "%{$search}%");
                  });
            });
        }
        
        // Apply unread filter
        if ($filter === 'unread') {
            $query->having('unread_count', '>', 0);
        }
        
        // Apply channel filter (filter patients who have messages in this channel)
        if ($channel) {
            $query->whereHas('messages', function($q) use ($channel) {
                $q->where('channel', $channel);
            });
        }
        
        // Order by latest message
        $query->orderByDesc(
            PatientMessage::select('created_at')
                ->whereColumn('patient_id', 'patients.id')
                ->orderByDesc('created_at')
                ->limit(1)
        );
        
        $patients = $query->get();
        
        // Transform data into conversation format
        $conversations = $patients->map(function($patient) {
            $latestMessage = $patient->messages->first();
            
            // Concatenate full name
            $fullName = trim(($patient->name ?? '') . ' ' . ($patient->last_name ?? ''));
            
            return [
                'id' => $patient->xid,
                'patientName' => $fullName ?: 'Unknown Patient',
                'patientPhone' => $patient->phone ?? '',
                'avatar' => $patient->profile_image ? Common::getFileUrl(null, $patient->profile_image) : asset('images/user.png'),
                'lastMessage' => $latestMessage ? $latestMessage->message : '',
                'lastMessageTime' => $latestMessage ? $this->formatMessageTime($latestMessage->created_at) : '',
                'lastMessageDate' => $latestMessage ? $latestMessage->created_at->toISOString() : null,
                'unread' => $patient->unread_count,
            ];
        });
        
        return response()->json([
            'success' => true,
            'data' => $conversations,
            'total' => $conversations->count(),
        ]);
    }
    
    /**
     * Format message time for display
     */
    private function formatMessageTime($datetime)
    {
        if (!$datetime) return '';
        
        $now = now();
        
        if ($datetime->isToday()) {
            return $datetime->format('g:i A');
        } elseif ($datetime->isYesterday()) {
            return 'Yesterday';
        } elseif ($datetime->isCurrentWeek()) {
            return $datetime->format('l'); // Day name
        } elseif ($datetime->isCurrentYear()) {
            return $datetime->format('M j');
        } else {
            return $datetime->format('M j, Y');
        }
    }

    public function modifyIndex($query) {
        $request = request();
        
        // Filter by patient
        if ($request->has('patient_id') && $request->patient_id != "") {
            $patientId = $this->getIdFromHash($request->patient_id);
            $query = $query->where('patient_id', $patientId);
        }

        // Filter by direction
        if ($request->has('direction') && $request->direction != "") {
            $query = $query->where('direction', $request->direction);
        }

        // Filter by status
        if ($request->has('status') && $request->status != "") {
            $query = $query->where('status', $request->status);
        }

        // Load relationships
        $query = $query->with(['sentBy:id,name', 'patient:id,name']);

        // Order by most recent first
        $query = $query->orderBy('created_at', 'desc');

        return $query;
    }

    /**
     * Send message to patient (SMS or WhatsApp)
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|string',
            'message' => 'required|string|max:1600',
            'phone' => 'required|string',
            'channel' => 'nullable|string|in:sms,whatsapp',
        ]);

        $patientId = $this->getIdFromHash($request->patient_id);
        $channel = $request->input('channel', 'sms');
        
        // Verify patient exists and belongs to company
        $patient = Patient::where('id', $patientId)
            ->where('company_id', company()->id)
            ->with('homeClinic') // Load clinic relationship
            ->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found',
            ], 404);
        }

        // Create message record
        $message = PatientMessage::create([
            'patient_id' => $patientId,
            'company_id' => company()->id,
            'sent_by_user_id' => auth('api')->user()->id,
            'message' => $request->message,
            'direction' => 'outbound',
            'status' => 'pending',
            'phone_number' => $request->phone,
            'channel' => $channel,
        ]);

        // Send via SMS/WhatsApp provider with clinic context
        try {
            // Initialize SMS service with clinic/company context
            // Priority: current clinic from header > patient's home clinic > company > global config
            $clinicId = null;
            
            // First, check if there's a clinic ID in the request header
            if ($request->header('x-clinic-id')) {
                $clinicId = $this->getIdFromHash($request->header('x-clinic-id'));
                Log::info("Using clinic from header: {$clinicId}");
            } 
            // Fall back to patient's home clinic
            elseif ($patient->home_clinic_id) {
                $clinicId = $patient->home_clinic_id;
                Log::info("Using patient's home clinic: {$clinicId}");
            }
            
            // Initialize SMS service with appropriate context
            if ($clinicId) {
                $smsService = SmsService::forClinic($clinicId, company()->id);
                Log::info("Sending message via clinic {$clinicId} for patient {$patientId}");
            } else {
                $smsService = SmsService::forCompany(company()->id);
                Log::info("Sending message via company " . company()->id . " for patient {$patientId}");
            }
            
            // Log which configuration is being used
            $configSource = $smsService->getConfigurationSource();
            Log::info("SMS Configuration source: " . json_encode($configSource));
            
            $result = $smsService->sendMessage($request->phone, $request->message, $channel);

            if ($result['success']) {
                $message->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'external_message_id' => $result['message_id'] ?? null,
                    'metadata' => array_merge($result['metadata'] ?? [], [
                        'config_source' => $configSource,
                    ]),
                ]);
                
                // Broadcast the new outbound message event
                // broadcast(new \App\Events\NewMessageReceived($message->fresh()->load('sentBy:id,name')))->toOthers();
            } else {
                $message->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                    'error_message' => $result['error'] ?? 'Unknown error',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send message: ' . $e->getMessage(), [
                'patient_id' => $patientId,
                'channel' => $channel,
            ]);
            
            $message->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
            ]);
        }

        return ApiResponse::make('Success', [
            'success' => $message->status !== 'failed',
            'message' => $message->status === 'failed' ? 'Message failed to send' : 'Message sent successfully',
            'data' => $message->fresh()->load('sentBy:id,name'),
        ]);    
    }

    /**
     * Get messages for a specific patient
     */
    public function getPatientMessages(Request $request, $patientId)
    {
        $patientIdDecoded = $this->getIdFromHash($patientId);
        
        // Verify patient exists and belongs to company
        $patient = Patient::where('id', $patientIdDecoded)
            ->where('company_id', company()->id)
            ->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found',
            ], 404);
        }

        $messages = PatientMessage::where('patient_id', $patientIdDecoded)
            ->with('sentBy:id,name')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Mark messages as read
     */
    public function markAsRead(Request $request, $patientId)
    {
        $patientIdDecoded = $this->getIdFromHash($patientId);
        
        // Verify patient exists and belongs to company
        $patient = Patient::where('id', $patientIdDecoded)
            ->where('company_id', company()->id)
            ->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found',
            ], 404);
        }

        // Mark all unread inbound messages for this patient as read
        $updatedCount = PatientMessage::where('patient_id', $patientIdDecoded)
            ->where('direction', 'inbound')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Messages marked as read',
            'updated_count' => $updatedCount,
        ]);
    }

    /**
     * Get unread message count for a patient
     */
    public function getUnreadCount(Request $request, $patientId)
    {
        $patientIdDecoded = $this->getIdFromHash($patientId);
        
        // Verify patient exists and belongs to company
        $patient = Patient::where('id', $patientIdDecoded)
            ->where('company_id', company()->id)
            ->first();

        if (!$patient) {
            return response()->json([
                'success' => false,
                'message' => 'Patient not found',
            ], 404);
        }

        $unreadCount = PatientMessage::where('patient_id', $patientIdDecoded)
            ->unread()
            ->count();

        return response()->json([
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Webhook endpoint for incoming SMS (from SMS provider)
     */
    public function receiveMessage(Request $request)
    {
        try {
            // For Twilio
            $from = $request->input('From');
            $body = $request->input('Body');
            $messageSid = $request->input('MessageSid');
            
            Log::info('Incoming SMS webhook received', [
                'from' => $from,
                'message_sid' => $messageSid,
                'body_length' => strlen($body ?? ''),
            ]);
            
            // Validate webhook signature (important for security in production)
            $smsService = app(SmsService::class);
            $signature = $request->header('X-Twilio-Signature');
            
            if ($signature && config('app.env') === 'production') {
                // Only enforce signature validation in production
                // In development with ngrok, the URL mismatch causes validation to fail
                $url = $request->fullUrl();
                $postData = $request->all();
                
                if (!$smsService->validateWebhookSignature($url, $postData, $signature)) {
                    Log::warning('Invalid Twilio webhook signature', ['from' => $from, 'url' => $url]);
                    return response()->json(['error' => 'Invalid signature'], 403);
                }
            } else if ($signature) {
                // Log signature check in development but don't block
                Log::info('Twilio signature present but validation skipped in development', ['from' => $from]);
            }
            
            if (!$from || !$body) {
                Log::warning('Invalid SMS webhook request - missing required fields');
                return response()->json(['success' => false, 'message' => 'Invalid request'], 400);
            }

            // Normalize phone number for matching (remove spaces, dashes, etc.)
            $normalizedFrom = preg_replace('/[^0-9+]/', '', $from);
            
            // Find patient by phone number - try multiple matching strategies
            $patient = Patient::whereHas('user', function($query) use ($from, $normalizedFrom) {
                $query->where(function($q) use ($from, $normalizedFrom) {
                    // Exact match
                    $q->where('phone', $from)
                      // Normalized match
                      ->orWhere('phone', $normalizedFrom)
                      // Last 10 digits match (for US numbers)
                      ->orWhere('phone', 'like', '%' . substr($normalizedFrom, -10));
                });
            })->first();
            
            if ($patient) {
                $message = PatientMessage::create([
                    'patient_id' => $patient->id,
                    'company_id' => $patient->company_id,
                    'message' => $body,
                    'direction' => 'inbound',
                    'status' => 'received',
                    'phone_number' => $from,
                    'external_message_id' => $messageSid,
                    'sent_at' => now(),
                ]);
                
                // Reload message to get all attributes including xid
                $message = $message->fresh()->load('patient', 'sentBy');
                
                // Broadcast the new message event
                broadcast(new \App\Events\NewMessageReceived($message));
                
                Log::info('Incoming SMS saved and broadcasted', [
                    'message_id' => $message->id,
                    'message_xid' => $message->xid,
                    'patient_id' => $patient->id,
                    'patient_xid' => $patient->xid,
                    'channel' => 'patient.' . $patient->xid,
                    'from' => $from,
                ]);
            } else {
                Log::warning('No patient found for incoming SMS', [
                    'from' => $from,
                    'normalized' => $normalizedFrom,
                ]);
            }
            
            // Return TwiML response (empty response tells Twilio we received it)
            return response('<Response></Response>', 200)
                ->header('Content-Type', 'text/xml');
        } catch (\Exception $e) {
            Log::error('Error receiving SMS: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);
            
            // Still return 200 to Twilio to prevent retries
            return response('<Response></Response>', 200)
                ->header('Content-Type', 'text/xml');
        }
    }
}
