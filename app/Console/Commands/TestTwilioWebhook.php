<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\PatientMessage;

class TestTwilioWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'twilio:test-webhook {--url= : The webhook URL to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Twilio webhook by simulating an incoming message';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = $this->option('url') ?: config('app.url') . '/api/webhooks/sms/incoming';
        
        $this->info('Testing Twilio Webhook...');
        $this->info('Webhook URL: ' . $url);
        $this->newLine();
        
        // Simulate Twilio webhook data
        $testData = [
            'MessageSid' => 'SM' . uniqid(),
            'From' => '+15555551234',
            'To' => config('services.twilio.from'),
            'Body' => 'Test message from webhook simulator at ' . now()->format('Y-m-d H:i:s'),
            'NumMedia' => '0',
            'FromCity' => 'Test City',
            'FromState' => 'CA',
            'FromCountry' => 'US',
        ];
        
        $this->info('Simulating incoming SMS with data:');
        $this->table(
            ['Field', 'Value'],
            [
                ['MessageSid', $testData['MessageSid']],
                ['From', $testData['From']],
                ['To', $testData['To']],
                ['Body', $testData['Body']],
            ]
        );
        $this->newLine();
        
        try {
            $response = Http::asForm()->post($url, $testData);
            
            if ($response->successful()) {
                $this->info('✅ Webhook responded successfully!');
                $this->info('Status Code: ' . $response->status());
                $this->info('Content-Type: ' . $response->header('Content-Type'));
                $this->newLine();
                
                // Check if message was saved
                $this->info('Checking database for saved message...');
                $message = PatientMessage::where('external_message_id', $testData['MessageSid'])->first();
                
                if ($message) {
                    $this->info('✅ Message saved to database!');
                    $this->table(
                        ['Field', 'Value'],
                        [
                            ['ID', $message->id],
                            ['Patient ID', $message->patient_id ?? 'N/A (no patient found)'],
                            ['Message', substr($message->message, 0, 50) . '...'],
                            ['Direction', $message->direction],
                            ['Status', $message->status],
                            ['Phone', $message->phone_number],
                        ]
                    );
                } else {
                    $this->warn('⚠️  Message not found in database.');
                    $this->info('This likely means no patient matched the phone number (+15555551234)');
                    $this->info('Create a test patient with this phone number to test end-to-end.');
                }
            } else {
                $this->error('❌ Webhook returned error!');
                $this->error('Status Code: ' . $response->status());
                $this->error('Response: ' . $response->body());
            }
        } catch (\Exception $e) {
            $this->error('❌ Failed to call webhook: ' . $e->getMessage());
            $this->newLine();
            $this->warn('Make sure:');
            $this->warn('1. Your application is running');
            $this->warn('2. The webhook URL is accessible');
            $this->warn('3. The route is properly configured in routes/web.php');
        }
        
        $this->newLine();
        $this->info('💡 Tips:');
        $this->info('- Check logs: tail -f storage/logs/laravel.log');
        $this->info('- Test with real SMS: Send a text to your Twilio number');
        $this->info('- Configure webhook in Twilio Console for production');
        
        return 0;
    }
}
