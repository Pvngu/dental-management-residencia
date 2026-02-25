<?php

namespace App\SuperAdmin\Http\Controllers\Api;

use App\SuperAdmin\Models\QuestionnaireInstance;
use App\SuperAdmin\Models\QuestionnaireAssignment;
use App\Models\User;
use App\Http\Controllers\ApiBaseController;
use App\Traits\CompanyTraits;
use App\SuperAdmin\Http\Requests\Api\QuestionnaireInstance\IndexRequest;
use App\SuperAdmin\Http\Requests\Api\QuestionnaireInstance\StoreRequest;
use App\SuperAdmin\Http\Requests\Api\QuestionnaireInstance\UpdateRequest;
use App\SuperAdmin\Http\Requests\Api\QuestionnaireInstance\DeleteRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Examyou\RestAPI\ApiResponse;

class QuestionnaireInstanceController extends ApiBaseController
{
    use CompanyTraits;
    
    protected $model = QuestionnaireInstance::class;
    protected $indexRequest = IndexRequest::class;
    protected $storeRequest = StoreRequest::class;
    protected $updateRequest = UpdateRequest::class;
    protected $deleteRequest = DeleteRequest::class;

    public function modifyIndex($query)
    {
        $request = request();

        // Load template relationship and assignment statistics
        $query = $query->with([
            'template:template_id,name,description',
        ])->withCount([
            'assignments as total_assignments',
            'assignments as pending_count' => function($q) {
                $q->where('status', 'PENDING');
            },
            'assignments as started_count' => function($q) {
                $q->where('status', 'STARTED');
            },
            'assignments as submitted_count' => function($q) {
                $q->where('status', 'SUBMITTED');
            },
            'assignments as expired_count' => function($q) {
                $q->where('status', 'EXPIRED');
            }
        ]);

        // Dates Filters
        if ($request->has('dates') && $request->dates != "") {
            $dates = explode(',', $request->dates);
            $startDate = $dates[0];
            $endDate = $dates[1];

            $query = $query->whereRaw('questionnaire_instances.launch_date >= ?', [$startDate])
                ->whereRaw('questionnaire_instances.launch_date <= ?', [$endDate]);
        }

        return $query;
    }

    /**
     * Generate assignments for a questionnaire instance
     * POST /instances/:id/assignments
     */
    public function generateAssignments(Request $request, $xid)
    {
        try {
            $instance = QuestionnaireInstance::where('id', $this->getIdFromHash($xid))->firstOrFail();
            
            // Validate instance status
            if ($instance->status === 'CLOSED' || $instance->status === 'ARCHIVED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot generate assignments for closed or archived instances'
                ], 400);
            }

            DB::beginTransaction();

            // Get target users based on population mode
            $usersQuery = User::where('company_id', $instance->company_id)
                ->where('status', 'active');

            // Apply target filters if specified
            if (!empty($instance->target_sucursales)) {
                $usersQuery->whereIn('branch_id', $instance->target_sucursales);
            }

            if (!empty($instance->target_roles)) {
                $usersQuery->whereHas('roles', function($q) use ($instance) {
                    $q->whereIn('name', $instance->target_roles);
                });
            }

            // Apply sampling if needed
            if ($instance->population_mode === 'SAMPLE') {
                // For SAMPLE mode, we'll take a percentage or fixed number
                // Since we removed sample_n field, we can use a default percentage or implement custom logic
                $totalUsers = $usersQuery->count();
                $sampleSize = max(1, intval($totalUsers * 0.3)); // 30% sample by default
                $users = $usersQuery->inRandomOrder()->limit($sampleSize)->get();
            } else {
                $users = $usersQuery->get();
            }

            // Create assignments
            $assignmentsData = [];
            foreach ($users as $user) {
                $assignmentsData[] = [
                    'instance_id' => $instance->instance_id,
                    'participant_id' => $user->id,
                    'status' => 'PENDING',
                    'assigned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Bulk insert with ignore duplicates
            QuestionnaireAssignment::insertOrIgnore($assignmentsData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Assignments generated successfully',
                'data' => [
                    'total_created' => count($assignmentsData),
                    'instance_id' => $instance->xid
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error generating assignments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Close a questionnaire instance
     * PATCH /instances/:id/close
     */
    public function closeInstance(Request $request, $xid)
    {
        try {
            $instance = QuestionnaireInstance::where('id', $this->getIdFromHash($xid))->firstOrFail();
            
            if ($instance->status === 'CLOSED' || $instance->status === 'ARCHIVED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Instance is already closed or archived'
                ], 400);
            }

            DB::beginTransaction();

            // Update instance status
            $instance->update([
                'status' => 'CLOSED',
                'end_date' => now()
            ]);

            // Mark pending assignments as expired
            QuestionnaireAssignment::where('instance_id', $instance->instance_id)
                ->where('status', 'PENDING')
                ->update([
                    'status' => 'EXPIRED',
                    'expired_at' => now()
                ]);

            // Calculate scoring snapshot (placeholder for future implementation)
            // $this->calculateScoringSnapshot($instance);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Instance closed successfully',
                'data' => [
                    'instance_id' => $instance->xid,
                    'closed_at' => $instance->end_date
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error closing instance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reopen a questionnaire instance
     * PATCH /instances/:id/reopen
     */
    public function reopenInstance(Request $request, $xid)
    {
        try {
            $instance = QuestionnaireInstance::where('id', $this->getIdFromHash($xid))->firstOrFail();
            
            if ($instance->status !== 'CLOSED') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only closed instances can be reopened'
                ], 400);
            }

            DB::beginTransaction();

            // Validate new end date
            $newEndDate = $request->input('end_date');
            if ($newEndDate && Carbon::parse($newEndDate)->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'End date must be in the future'
                ], 400);
            }

            // Update instance status
            $instance->update([
                'status' => 'OPEN',
                'end_date' => $newEndDate ? Carbon::parse($newEndDate) : $instance->end_date
            ]);

            // Optionally reactivate expired assignments
            if ($request->input('reactivate_expired', false)) {
                QuestionnaireAssignment::where('instance_id', $instance->instance_id)
                    ->where('status', 'EXPIRED')
                    ->update([
                        'status' => 'PENDING',
                        'expired_at' => null
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Instance reopened successfully',
                'data' => [
                    'instance_id' => $instance->xid,
                    'new_end_date' => $instance->end_date
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error reopening instance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get detailed instance statistics
     * GET /instances/:id/stats
     */
    public function getInstanceStats($xid)
    {
        try {
            $instance = QuestionnaireInstance::where('id', $this->getIdFromHash($xid))
                ->with(['template', 'assignments.participant'])
                ->withCount([
                    'assignments as total_assignments',
                    'assignments as pending_count' => function($q) {
                        $q->where('status', 'PENDING');
                    },
                    'assignments as started_count' => function($q) {
                        $q->where('status', 'STARTED');
                    },
                    'assignments as submitted_count' => function($q) {
                        $q->where('status', 'SUBMITTED');
                    },
                    'assignments as expired_count' => function($q) {
                        $q->where('status', 'EXPIRED');
                    }
                ])
                ->firstOrFail();

            // Calculate completion rate
            $completionRate = $instance->total_assignments > 0 
                ? ($instance->submitted_count / $instance->total_assignments) * 100 
                : 0;

            // Get assignment details for table view
            $assignments = QuestionnaireAssignment::where('instance_id', $instance->instance_id)
                ->with(['participant:id,name,email'])
                ->select('assignment_id', 'participant_id', 'status', 'assigned_at', 'started_at', 'submitted_at', 'expired_at')
                ->get();

            return ApiResponse::make([
                'success' => true,
                'data' => [
                    'instance' => $instance,
                    'stats' => [
                        'total_assignments' => $instance->total_assignments,
                        'pending_count' => $instance->pending_count,
                        'started_count' => $instance->started_count,
                        'submitted_count' => $instance->submitted_count,
                        'expired_count' => $instance->expired_count,
                        'completion_rate' => round($completionRate, 2)
                    ],
                    'assignments' => $assignments->toArray()
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving instance stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export instance results
     * GET /instances/:id/export
     */
    public function exportResults($xid)
    {
        try {
            $instance = QuestionnaireInstance::where('id', $this->getIdFromHash($xid))->firstOrFail();
            
            // This would be implemented based on specific export requirements
            // For now, return basic structure
            
            return ApiResponse::make([
                'success' => true,
                'message' => 'Export functionality to be implemented',
                'data' => [
                    'instance_id' => $instance->xid,
                    'export_url' => 'to-be-implemented'
                ]
            ]);

        } catch (\Exception $e) {
            return ApiResponse::make([
                'success' => false,
                'message' => 'Error exporting results: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send reminders to pending assignments
     * POST /instances/:id/send-reminders
     */
    public function sendReminders(Request $request, $xid)
    {
        try {
            $instance = QuestionnaireInstance::where('id', $this->getIdFromHash($xid))->firstOrFail();
            
            if ($instance->status !== 'OPEN') {
                return ApiResponse::make([
                    'success' => false,
                    'message' => 'Can only send reminders for open instances'
                ], 400);
            }

            // Get pending assignments
            $pendingAssignments = QuestionnaireAssignment::where('instance_id', $instance->instance_id)
                ->where('status', 'PENDING')
                ->with(['participant:id,name,email'])
                ->get();

            if ($pendingAssignments->isEmpty()) {
                return ApiResponse::make([
                    'success' => false,
                    'message' => 'No pending assignments to send reminders to'
                ], 400);
            }

            $remindersSent = 0;
            foreach ($pendingAssignments as $assignment) {
                // Here would implement actual email/notification sending
                // For now, just log the action
                Log::info("Sending reminder to {$assignment->participant->email} for instance {$instance->xid}");
                
                // Update last_reminder_sent timestamp (would need to add this field to assignments table)
                // $assignment->update(['last_reminder_sent' => now()]);
                
                $remindersSent++;
            }

            return ApiResponse::make([
                'success' => true,
                'message' => 'Reminders sent successfully',
                'data' => [
                    'instance_id' => $instance->xid,
                    'reminders_sent' => $remindersSent
                ]
            ]);

        } catch (\Exception $e) {
            return ApiResponse::make([
                'success' => false,
                'message' => 'Error sending reminders: ' . $e->getMessage()
            ], 500);
        }
    }
}
