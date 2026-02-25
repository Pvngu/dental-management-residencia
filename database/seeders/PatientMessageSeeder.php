<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PatientMessage;
use App\Models\Patient;
use App\Models\User;
use App\Models\Company;
use Carbon\Carbon;

class PatientMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample messages for variety
        $smsMessages = [
            'Hi, this is a reminder for your appointment tomorrow at 10:00 AM.',
            'Your dental cleaning is scheduled for next Monday. Please confirm.',
            'Thank you for choosing our dental clinic. See you soon!',
            'Please bring your insurance card to your next appointment.',
            'We have moved your appointment to 2:00 PM. Please confirm.',
            'Your treatment plan is ready for review. Call us to schedule.',
            'Reminder: Please take your prescribed medication as instructed.',
            'Your lab results are in. Please call to schedule a follow-up.',
            'We hope your treatment went well today. Feel free to reach out with questions.',
            'Please arrive 15 minutes early for your appointment tomorrow.',
            'Your insurance pre-authorization has been approved.',
            'Weather alert: We are open normal hours despite the weather.',
            'New patient forms are available online. Please complete before your visit.',
            'Thank you for your payment. Your account is now current.',
            'We have a cancellation available tomorrow if you want to move up your appointment.',
            'Please call us back to reschedule your appointment.',
            'Your prescription is ready for pickup at our office.',
            'Post-treatment care instructions have been sent to your email.',
            'We appreciate your 5-star review! Thank you!',
            'Please avoid hard foods for the next 24 hours after your procedure.',
        ];

        $whatsappMessages = [
            'Hello! Your appointment is confirmed for tomorrow at 10:00 AM.',
            'Hi there! Just a friendly reminder about your dental cleaning next Monday.',
            'Thanks for choosing our clinic! Looking forward to seeing you soon.',
            'Don\'t forget to bring your insurance card to your next visit.',
            'Your appointment has been moved to 2:00 PM. Is this time still good for you?',
            'Your personalized treatment plan is ready! Call us when convenient.',
            'Remember to take your medication as prescribed. Feel better soon!',
            'Great news! Your lab results look good. Let\'s schedule your follow-up.',
            'Hope you\'re feeling well after today\'s treatment! Questions? Just ask.',
            'Quick reminder: Please arrive 15 minutes early tomorrow.',
            'Excellent news! Your insurance approved the treatment.',
            'We\'re open despite the weather! Drive safely.',
            'New patient forms are online now. Please complete before your visit.',
            'Payment received! Thank you. Your account is current.',
            'We have a cancellation tomorrow - interested in moving up your appointment?',
            'Please give us a call back about rescheduling.',
            'Your prescription is ready for pickup.',
            'Check your email for post-treatment care instructions.',
            'Thank you for the amazing review! You made our day!',
            'Remember: soft foods only for 24 hours after your procedure.',
        ];

        $patientResponses = [
            'Thank you, I\'ll be there on time.',
            'Can we reschedule for next week?',
            'Yes, confirmed. See you then.',
            'I have a question about my treatment.',
            'The appointment time works great!',
            'Thanks for the reminder.',
            'I need to cancel my appointment.',
            'What should I expect during the procedure?',
            'I\'m running a few minutes late.',
            'Thank you for the great service today!',
            'Will my insurance cover this?',
            'I forgot to bring my forms.',
            'Can you send me the payment details?',
            'I\'m feeling better after the treatment.',
            'When is my next appointment?',
            'Thanks for the quick response.',
            'I have some post-treatment questions.',
            'Perfect, I\'ll be there early.',
            'Can I get a receipt for my payment?',
            'The medication is helping a lot.',
        ];

        // Get all patients and companies
        $companies = Company::all();
        
        foreach ($companies as $company) {
            $patients = Patient::where('company_id', $company->id)->get();
            $users = User::where('company_id', $company->id)->get();
            
            if ($patients->isEmpty() || $users->isEmpty()) {
                continue;
            }

            foreach ($patients as $patient) {
                // Generate 10-25 messages per patient
                $messageCount = rand(10, 25);
                
                for ($i = 0; $i < $messageCount; $i++) {
                    $isInbound = rand(0, 3) === 0; // 25% chance of inbound message
                    $channel = rand(0, 1) === 0 ? 'sms' : 'whatsapp';
                    
                    // Select appropriate message based on channel
                    if ($channel === 'sms') {
                        $message = $isInbound ? 
                            $patientResponses[array_rand($patientResponses)] : 
                            $smsMessages[array_rand($smsMessages)];
                    } else {
                        $message = $isInbound ? 
                            $patientResponses[array_rand($patientResponses)] : 
                            $whatsappMessages[array_rand($whatsappMessages)];
                    }
                    
                    // Determine status based on direction
                    $possibleStatuses = $isInbound ? 
                        ['received'] : 
                        ['pending', 'sent', 'delivered', 'failed'];
                    
                    $status = $possibleStatuses[array_rand($possibleStatuses)];
                    
                    // Create timestamps based on status
                    $createdAt = Carbon::now()->subDays(rand(1, 90))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
                    $sentAt = null;
                    $deliveredAt = null;
                    $failedAt = null;
                    $readAt = null;
                    
                    if ($status !== 'pending') {
                        $sentAt = $createdAt->copy()->addMinutes(rand(1, 5));
                        
                        if ($status === 'delivered') {
                            $deliveredAt = $sentAt->copy()->addMinutes(rand(1, 30));
                            // 70% chance of being read if delivered
                            if (rand(0, 9) < 7) {
                                $readAt = $deliveredAt->copy()->addMinutes(rand(1, 1440)); // 1 minute to 24 hours
                            }
                        } elseif ($status === 'failed') {
                            $failedAt = $sentAt->copy()->addMinutes(rand(1, 10));
                        } elseif ($status === 'received') {
                            // For inbound messages, 60% chance of being read
                            if (rand(0, 9) < 6) {
                                $readAt = $createdAt->copy()->addMinutes(rand(1, 240)); // 1 minute to 4 hours
                            }
                        }
                    }
                    
                    // Generate phone number (simplified format)
                    $phoneNumber = '+1' . rand(200, 999) . rand(200, 999) . rand(1000, 9999);
                    
                    // Metadata for additional context
                    $metadata = [
                        'user_agent' => $channel === 'whatsapp' ? 'WhatsApp/2.21.0' : 'SMS Gateway/1.0',
                        'location' => $company->timezone ?? 'UTC',
                        'priority' => rand(0, 4) === 0 ? 'high' : 'normal',
                    ];
                    
                    if ($channel === 'whatsapp') {
                        $metadata['thread_id'] = 'thread_' . $patient->id . '_' . rand(1000, 9999);
                    }
                    
                    PatientMessage::create([
                        'patient_id' => $patient->id,
                        'company_id' => $company->id,
                        'sent_by_user_id' => $isInbound ? null : $users->random()->id,
                        'message' => $message,
                        'direction' => $isInbound ? 'inbound' : 'outbound',
                        'status' => $status,
                        'phone_number' => $phoneNumber,
                        'channel' => $channel,
                        'external_message_id' => $status !== 'pending' ? 'ext_msg_' . rand(100000, 999999) : null,
                        'metadata' => $metadata,
                        'sent_at' => $sentAt,
                        'delivered_at' => $deliveredAt,
                        'failed_at' => $failedAt,
                        'read_at' => $readAt,
                        'error_message' => $status === 'failed' ? 'Network timeout after 30 seconds' : null,
                        'created_at' => $createdAt,
                        'updated_at' => $deliveredAt ?? $sentAt ?? $createdAt,
                    ]);
                }
            }
        }
        
        $this->command->info('Patient messages seeded successfully!');
        
        // Display some statistics
        $totalMessages = PatientMessage::count();
        $unreadMessages = PatientMessage::whereNull('read_at')->count();
        $inboundMessages = PatientMessage::where('direction', 'inbound')->count();
        $whatsappMessages = PatientMessage::where('channel', 'whatsapp')->count();
        
        $this->command->info("Statistics:");
        $this->command->info("- Total messages: {$totalMessages}");
        $this->command->info("- Unread messages: {$unreadMessages}");
        $this->command->info("- Inbound messages: {$inboundMessages}");
        $this->command->info("- WhatsApp messages: {$whatsappMessages}");
        $this->command->info("- SMS messages: " . ($totalMessages - $whatsappMessages));
    }
}