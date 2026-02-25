<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SmsService;

class TestTwilioSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twilio:test {phone?} {--message=Test message from 800dent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Twilio SMS configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $smsService = app(SmsService::class);
        
        // Check if Twilio is enabled
        $this->info('Testing Twilio Configuration...');
        $this->newLine();
        
        if (!$smsService->isEnabled()) {
            $this->warn('⚠️  Twilio is not enabled or not configured properly.');
            $this->info('Current configuration:');
            $this->table(
                ['Setting', 'Value'],
                [
                    ['TWILIO_ENABLED', config('services.twilio.enabled') ? 'true' : 'false'],
                    ['TWILIO_SID', config('services.twilio.sid') ? 'Set (***' . substr(config('services.twilio.sid'), -4) . ')' : 'Not set'],
                    ['TWILIO_AUTH_TOKEN', config('services.twilio.token') ? 'Set' : 'Not set'],
                    ['TWILIO_PHONE_NUMBER', config('services.twilio.from') ?: 'Not set'],
                ]
            );
            $this->newLine();
            $this->info('To enable Twilio, add these to your .env file:');
            $this->info('TWILIO_ENABLED=true');
            $this->info('TWILIO_SID=your_account_sid');
            $this->info('TWILIO_AUTH_TOKEN=your_auth_token');
            $this->info('TWILIO_PHONE_NUMBER=+1234567890');
            $this->newLine();
            $this->info('For development without Twilio, messages will be logged (mock mode).');
            
            return 0;
        }
        
        $this->info('✅ Twilio is enabled and configured!');
        $this->table(
            ['Setting', 'Value'],
            [
                ['TWILIO_ENABLED', 'true'],
                ['TWILIO_SID', '***' . substr(config('services.twilio.sid'), -4)],
                ['TWILIO_AUTH_TOKEN', 'Set'],
                ['TWILIO_PHONE_NUMBER', config('services.twilio.from')],
            ]
        );
        $this->newLine();
        
        // Test sending if phone number provided
        $phone = $this->argument('phone');
        if ($phone) {
            if ($this->confirm('Do you want to send a test SMS to ' . $phone . '?', true)) {
                $message = $this->option('message');
                
                $this->info('Sending test message...');
                $result = $smsService->send($phone, $message);
                
                if ($result['success']) {
                    $this->info('✅ Message sent successfully!');
                    $this->table(
                        ['Field', 'Value'],
                        [
                            ['Message ID', $result['message_id'] ?? 'N/A'],
                            ['Status', $result['status'] ?? 'N/A'],
                            ['Price', $result['metadata']['price'] ?? 'N/A'],
                            ['Segments', $result['metadata']['num_segments'] ?? 'N/A'],
                        ]
                    );
                } else {
                    $this->error('❌ Failed to send message: ' . ($result['error'] ?? 'Unknown error'));
                }
            }
        } else {
            $this->info('To test sending a message, run:');
            $this->info('php artisan twilio:test +1234567890');
        }
        
        return 0;
    }
}
