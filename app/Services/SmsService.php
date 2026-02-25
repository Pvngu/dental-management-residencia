<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ClinicLocation;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class SmsService
{
    protected $client;
    protected $fromNumber;
    protected $whatsappFromNumber;
    protected $enabled;
    protected $companyId;
    protected $clinicId;
    protected $twilioConfig;
    
    /**
     * Initialize SMS service with optional company/clinic context
     * 
     * @param int|null $companyId Company ID
     * @param int|null $clinicId Clinic Location ID
     */
    public function __construct($companyId = null, $clinicId = null)
    {
        $this->companyId = $companyId;
        $this->clinicId = $clinicId;
        
        // Load Twilio configuration based on context
        $this->twilioConfig = $this->loadTwilioConfig();
        
        $this->enabled = $this->twilioConfig['enabled'] ?? false;
        
        if ($this->enabled) {
            try {
                // Only initialize Twilio if credentials are configured
                if (class_exists(\Twilio\Rest\Client::class)) {
                    $this->client = new \Twilio\Rest\Client(
                        $this->twilioConfig['sid'],
                        $this->twilioConfig['token']
                    );
                    $this->fromNumber = $this->twilioConfig['from'];
                    $this->whatsappFromNumber = $this->twilioConfig['whatsapp_from'];
                }
            } catch (Exception $e) {
                Log::warning('Twilio client initialization failed: ' . $e->getMessage());
                $this->enabled = false;
            }
        }
    }
    
    /**
     * Load Twilio configuration with fallback priority:
     * 1. Clinic-specific phone number (if clinic uses own Twilio) + global credentials
     * 2. Company-specific phone number + global credentials
     * 3. Global environment configuration
     * 
     * Note: Twilio SID and Auth Token are always loaded from .env (single account)
     * Only phone numbers can be customized per company/clinic
     * 
     * @return array Configuration array with sid, token, from, whatsapp_from, enabled
     */
    protected function loadTwilioConfig()
    {
        // Always use global Twilio credentials from .env
        $globalSid = config('services.twilio.sid');
        $globalToken = config('services.twilio.token');
        $globalEnabled = config('services.twilio.enabled', false);
        
        // Try clinic-specific phone number first
        if ($this->clinicId) {
            $clinic = ClinicLocation::find($this->clinicId);
            
            if ($clinic && $clinic->use_own_twilio && $clinic->twilio_phone_number) {
                Log::info("Using clinic-specific phone number for clinic ID: {$this->clinicId}");
                return [
                    'sid' => $globalSid,
                    'token' => $globalToken,
                    'from' => $clinic->twilio_phone_number,
                    'whatsapp_from' => $clinic->twilio_whatsapp_number ?: $clinic->twilio_phone_number,
                    'enabled' => $globalEnabled,
                ];
            }
        }
        
        // Try company-specific phone number
        if ($this->companyId) {
            $company = Company::withoutGlobalScope('company')->find($this->companyId);
            
            if ($company && $company->twilio_enabled && $company->twilio_phone_number) {
                Log::info("Using company-specific phone number for company ID: {$this->companyId}");
                return [
                    'sid' => $globalSid,
                    'token' => $globalToken,
                    'from' => $company->twilio_phone_number,
                    'whatsapp_from' => $company->twilio_whatsapp_number ?: $company->twilio_phone_number,
                    'enabled' => $globalEnabled,
                ];
            }
        }
        
        // Fall back to global environment configuration
        Log::info("Using global Twilio config from environment");
        return [
            'sid' => $globalSid,
            'token' => $globalToken,
            'from' => config('services.twilio.from'),
            'whatsapp_from' => config('services.twilio.whatsapp_from'),
            'enabled' => $globalEnabled,
        ];
    }
    
    /**
     * Send SMS message
     * 
     * @param string $to Recipient phone number
     * @param string $message Message content
     * @return array Result array with success status
     */
    public function send($to, $message)
    {
        return $this->sendMessage($to, $message, 'sms');
    }
    
    /**
     * Send WhatsApp message
     * 
     * @param string $to Recipient phone number
     * @param string $message Message content
     * @return array Result array with success status
     */
    public function sendWhatsApp($to, $message)
    {
        return $this->sendMessage($to, $message, 'whatsapp');
    }
    
    /**
     * Send message via specified channel
     * 
     * @param string $to Recipient phone number
     * @param string $message Message content
     * @param string $channel 'sms' or 'whatsapp'
     * @return array Result array with success status
     */
    public function sendMessage($to, $message, $channel = 'sms')
    {
        // If SMS is not enabled, return mock success
        if (!$this->enabled || !$this->client) {
            Log::info("{$channel} Service not enabled. Would send to {$to}: {$message}");
            return [
                'success' => true,
                'message_id' => 'mock_' . uniqid(),
                'status' => 'sent',
                'metadata' => ['mock' => true, 'channel' => $channel],
            ];
        }

        try {
            // Format phone numbers for WhatsApp (must include whatsapp: prefix)
            if ($channel === 'whatsapp') {
                $fromNumber = $this->whatsappFromNumber ?: ('whatsapp:' . $this->fromNumber);
                $toNumber = $this->formatWhatsAppNumber($to);
                
                // Make sure from number has whatsapp: prefix
                if (strpos($fromNumber, 'whatsapp:') !== 0) {
                    $fromNumber = 'whatsapp:' . $fromNumber;
                }
            } else {
                $fromNumber = $this->fromNumber;
                $toNumber = $to;
            }
            
            $result = $this->client->messages->create($toNumber, [
                'from' => $fromNumber,
                'body' => $message
            ]);
            
            return [
                'success' => true,
                'message_id' => $result->sid,
                'status' => $result->status,
                'channel' => $channel,
                'metadata' => [
                    'price' => $result->price,
                    'price_unit' => $result->priceUnit,
                    'num_segments' => $result->numSegments,
                    'channel' => $channel,
                ],
            ];
        } catch (Exception $e) {
            Log::error("{$channel} send failed: " . $e->getMessage(), [
                'to' => $to,
                'message' => $message,
                'channel' => $channel,
            ]);
            
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'channel' => $channel,
            ];
        }
    }
    
    /**
     * Format phone number for WhatsApp
     * 
     * @param string $phone
     * @return string
     */
    protected function formatWhatsAppNumber($phone)
    {
        // Remove any existing whatsapp: prefix
        $phone = str_replace('whatsapp:', '', $phone);
        
        // Ensure it has whatsapp: prefix
        return 'whatsapp:' . $phone;
    }
    
    /**
     * Check if SMS service is enabled and configured
     * 
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled && $this->client !== null;
    }
    
    /**
     * Check if WhatsApp is configured
     * 
     * @return bool
     */
    public function isWhatsAppEnabled()
    {
        return $this->isEnabled() && ($this->whatsappFromNumber || $this->fromNumber);
    }
    
    /**
     * Get available channels
     * 
     * @return array
     */
    public function getAvailableChannels()
    {
        $channels = [];
        
        if ($this->isEnabled()) {
            $channels[] = 'sms';
            
            if ($this->isWhatsAppEnabled()) {
                $channels[] = 'whatsapp';
            }
        }
        
        return $channels;
    }
    
    /**
     * Get configuration source information
     * Returns which configuration is being used (clinic, company, or global)
     * 
     * @return array
     */
    public function getConfigurationSource()
    {
        if ($this->clinicId) {
            $clinic = ClinicLocation::find($this->clinicId);
            if ($clinic && $clinic->use_own_twilio && $clinic->twilio_phone_number) {
                return [
                    'source' => 'clinic',
                    'clinic_id' => $this->clinicId,
                    'clinic_name' => $clinic->name,
                    'phone_number' => $this->fromNumber,
                ];
            }
        }
        
        if ($this->companyId) {
            $company = Company::withoutGlobalScope('company')->find($this->companyId);
            if ($company && $company->twilio_enabled && $company->twilio_phone_number) {
                return [
                    'source' => 'company',
                    'company_id' => $this->companyId,
                    'company_name' => $company->name,
                    'phone_number' => $this->fromNumber,
                ];
            }
        }
        
        return [
            'source' => 'global',
            'phone_number' => $this->fromNumber,
        ];
    }
    
    /**
     * Static factory method to create service with company context
     * 
     * @param int $companyId
     * @return static
     */
    public static function forCompany($companyId)
    {
        return new static($companyId, null);
    }
    
    /**
     * Static factory method to create service with clinic context
     * 
     * @param int $clinicId
     * @param int|null $companyId Optional company ID (will be loaded from clinic if not provided)
     * @return static
     */
    public static function forClinic($clinicId, $companyId = null)
    {
        if (!$companyId) {
            $clinic = ClinicLocation::find($clinicId);
            $companyId = $clinic ? $clinic->company_id : null;
        }
        
        return new static($companyId, $clinicId);
    }
    
    /**
     * Validate Twilio webhook signature
     * 
     * @param string $url Full URL of the webhook
     * @param array $postData POST data from the webhook
     * @param string $signature X-Twilio-Signature header
     * @return bool
     */
    public function validateWebhookSignature($url, $postData, $signature)
    {
        if (!$this->enabled || !class_exists(\Twilio\Security\RequestValidator::class)) {
            return true; // Skip validation if Twilio is not configured
        }

        try {
            $validator = new \Twilio\Security\RequestValidator(config('services.twilio.token'));
            return $validator->validate($signature, $url, $postData);
        } catch (Exception $e) {
            Log::error('Webhook signature validation failed: ' . $e->getMessage());
            return false;
        }
    }
}
