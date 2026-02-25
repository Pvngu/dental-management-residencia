<?php

namespace App\Console\Commands;

use App\Events\NewMessageReceived;
use App\Models\PatientMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestBroadcast extends Command
{
    protected $signature = 'test:broadcast {message_id?}';
    protected $description = 'Test broadcasting a message';

    public function handle()
    {
        $messageId = $this->argument('message_id');
        
        if ($messageId) {
            $message = PatientMessage::find($messageId);
        } else {
            $message = PatientMessage::latest()->first();
        }
        
        if (!$message) {
            $this->error('No message found');
            return 1;
        }
        
        $this->info("Message ID: {$message->id}");
        $this->info("Message XID: {$message->xid}");
        $this->info("Patient XID: {$message->x_patient_id}");
        $this->info("Company ID: {$message->company_id}");
        
        $this->info("\nBroadcasting to channels:");
        $this->info("  - private-company.{$message->company_id}");
        $this->info("  - private-patient.{$message->x_patient_id}");
        
        try {
            $event = new NewMessageReceived($message);
            
            $this->info("\nEvent broadcastOn(): ");
            foreach ($event->broadcastOn() as $channel) {
                $this->info("  - " . $channel->name);
            }
            
            $this->info("\nEvent broadcastAs(): " . $event->broadcastAs());
            
            $this->info("\nDispatching event...");
            broadcast($event);
            
            $this->info("\n✅ Event dispatched successfully!");
            
            Log::info('Test broadcast dispatched', [
                'message_id' => $message->id,
                'channels' => array_map(fn($c) => $c->name, $event->broadcastOn()),
            ]);
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
        
        return 0;
    }
}
