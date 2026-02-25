<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Email;
use App\Models\Patient;
use App\Models\Company;
use App\Models\User;
use App\Models\Doctor;
use Carbon\Carbon;

class EmailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all patients, companies, and doctors
        $patients = Patient::with('user')->get();
        $companies = Company::all();
        $doctors = Doctor::with('user')->get();

        if ($patients->isEmpty() || $companies->isEmpty()) {
            $this->command->warn('No patients or companies found. Please run patients and companies seeders first.');
            return;
        }

        $emailTemplates = [
            // Appointment Reminders
            [
                'subject' => 'Appointment Reminder - Tomorrow at {time}',
                'body' => "Dear {patient_name},\n\nThis is a friendly reminder about your upcoming dental appointment scheduled for tomorrow at {time}.\n\nPlease arrive 15 minutes early to complete any necessary paperwork. If you need to reschedule, please contact us at least 24 hours in advance.\n\nWe look forward to seeing you!\n\nBest regards,\nYour Dental Care Team",
                'status' => 'sent',
                'days_ago' => 1,
            ],
            [
                'subject' => 'Your Appointment is Confirmed',
                'body' => "Hello {patient_name},\n\nWe are pleased to confirm your dental appointment:\n\nDate: {date}\nTime: {time}\nDoctor: Dr. Smith\n\nPlease bring your insurance card and any relevant medical records. If you have any questions, feel free to contact us.\n\nThank you for choosing our dental practice!\n\nWarm regards,\nReception Team",
                'status' => 'sent',
                'days_ago' => 7,
            ],
            
            // Treatment Follow-ups
            [
                'subject' => 'Post-Treatment Care Instructions',
                'body' => "Dear {patient_name},\n\nThank you for visiting us today. Here are your post-treatment care instructions:\n\n• Take prescribed medication as directed\n• Avoid hot foods and beverages for the next 24 hours\n• Do not drink through a straw for 48 hours\n• Gently rinse with warm salt water after meals\n• Contact us immediately if you experience severe pain or unusual symptoms\n\nIf you have any concerns, please don't hesitate to reach out.\n\nWishing you a speedy recovery!\n\nBest regards,\nDr. Johnson and Team",
                'status' => 'sent',
                'days_ago' => 3,
            ],
            [
                'subject' => 'How are you feeling? Follow-up Check',
                'body' => "Hi {patient_name},\n\nWe hope you're recovering well after your recent dental procedure. We wanted to check in and see how you're feeling.\n\nIf you're experiencing any discomfort, swelling, or have questions about your recovery, please contact us right away. Your comfort and health are our top priorities.\n\nYour next follow-up appointment is scheduled for {date}.\n\nTake care!\n\nBest wishes,\nYour Dental Team",
                'status' => 'sent',
                'days_ago' => 5,
            ],

            // Payment & Billing
            [
                'subject' => 'Payment Receipt - Invoice #{invoice_number}',
                'body' => "Dear {patient_name},\n\nThank you for your payment. This email serves as your receipt.\n\nTransaction Details:\n• Amount: \${amount}\n• Transaction ID: {transaction_id}\n• Date: {date}\n• Payment Method: {payment_method}\n\nYour current account balance is: \$0.00\n\nIf you have any questions about this payment or need a detailed invoice, please contact our billing department.\n\nThank you for your business!\n\nSincerely,\nBilling Department",
                'status' => 'sent',
                'days_ago' => 2,
            ],
            [
                'subject' => 'Treatment Plan Summary & Cost Estimate',
                'body' => "Hello {patient_name},\n\nAs discussed during your recent visit, please find below a summary of your treatment plan:\n\nRecommended Treatments:\n• Root Canal Therapy - Tooth #14\n• Crown Placement\n• Deep Cleaning (2 sessions)\n\nEstimated Total: \${total_cost}\nInsurance Coverage: \${insurance_coverage}\nYour Estimated Out-of-Pocket: \${out_of_pocket}\n\nWe offer flexible payment plans to help make your dental care more affordable. Please contact our office to discuss options.\n\nBest regards,\nFinancial Coordinator",
                'status' => 'sent',
                'days_ago' => 10,
            ],

            // Welcome & Onboarding
            [
                'subject' => 'Welcome to Our Dental Family!',
                'body' => "Dear {patient_name},\n\nWelcome to our dental practice! We're thrilled to have you as a new patient.\n\nTo help us provide you with the best possible care, please:\n• Complete your medical history form (attached)\n• Bring your insurance card to your first visit\n• Arrive 15 minutes early for paperwork\n• Bring a list of current medications\n\nOur team is dedicated to making your dental experience comfortable and stress-free. If you have any questions before your first visit, please don't hesitate to call us.\n\nWe look forward to meeting you!\n\nWarm welcome,\nThe Dental Care Team",
                'status' => 'sent',
                'days_ago' => 14,
            ],

            // Health Tips & Education
            [
                'subject' => 'Monthly Dental Health Tips',
                'body' => "Hello {patient_name},\n\nHere are this month's dental health tips to keep your smile bright:\n\n1. Brush twice daily for 2 minutes each time\n2. Floss at least once a day\n3. Use fluoride toothpaste\n4. Limit sugary snacks and beverages\n5. Replace your toothbrush every 3-4 months\n6. Don't forget to clean your tongue!\n\nRemember, regular dental checkups are essential for maintaining optimal oral health. Schedule your next cleaning if you haven't already!\n\nStay healthy!\n\nYour Dental Health Team",
                'status' => 'sent',
                'days_ago' => 20,
            ],

            // Recalls & Reminders
            [
                'subject' => 'Time for Your 6-Month Cleaning!',
                'body' => "Hi {patient_name},\n\nIt's been 6 months since your last dental cleaning! Regular cleanings help prevent cavities, gum disease, and other dental problems.\n\nWe recommend scheduling your next appointment soon. Our available times are:\n\n• Monday-Friday: 8:00 AM - 6:00 PM\n• Saturday: 9:00 AM - 2:00 PM\n\nCall us at (555) 123-4567 or reply to this email to book your preferred time slot.\n\nWe look forward to seeing you soon!\n\nBest regards,\nDental Hygiene Team",
                'status' => 'sent',
                'days_ago' => 4,
            ],

            // Draft Emails
            [
                'subject' => 'Special Promotion - Teeth Whitening',
                'body' => "Dear {patient_name},\n\nFor a limited time, we're offering a special promotion on professional teeth whitening!\n\nGet a brighter, whiter smile with our in-office whitening treatment:\n• Regular Price: \$500\n• Promotional Price: \$350\n• Offer expires: {expiry_date}\n\nThis professional treatment can brighten your smile by several shades in just one visit!\n\nCall us to schedule your appointment and take advantage of this limited-time offer.\n\nSmile brighter!\n\nYour Cosmetic Dentistry Team",
                'status' => 'draft',
                'days_ago' => null,
            ],
            [
                'subject' => 'Important: Insurance Information Update Needed',
                'body' => "Dear {patient_name},\n\nOur records show that your dental insurance information may need updating. To ensure seamless processing of your claims, please provide us with:\n\n• Updated insurance card (front and back)\n• Current policy number\n• Any changes to your coverage\n\nYou can:\n• Email copies to info@dentalcare.com\n• Upload via our patient portal\n• Bring them to your next appointment\n\nThank you for helping us keep your information current!\n\nBest regards,\nInsurance Coordinator",
                'status' => 'draft',
                'days_ago' => null,
            ],
        ];

        $this->command->info('Creating realistic email records...');
        $createdCount = 0;

        foreach ($patients as $patient) {
            if (!$patient->user || !$patient->user->email) {
                continue;
            }
            
            // Create 2-4 emails per patient
            $emailCount = rand(2, 4);
            $selectedTemplates = collect($emailTemplates)->random($emailCount);

            foreach ($selectedTemplates as $template) {
                // Replace placeholders with actual data
                $body = str_replace(
                    [
                        '{patient_name}',
                        '{time}',
                        '{date}',
                        '{invoice_number}',
                        '{amount}',
                        '{transaction_id}',
                        '{payment_method}',
                        '{total_cost}',
                        '{insurance_coverage}',
                        '{out_of_pocket}',
                        '{expiry_date}',
                    ],
                    [
                        $patient->user->name . ' ' . ($patient->user->last_name ?? ''),
                        '10:30 AM',
                        Carbon::now()->addDays(5)->format('F j, Y'),
                        'INV-' . rand(10000, 99999),
                        number_format(rand(50, 500), 2),
                        'TXN-' . rand(100000, 999999),
                        'Credit Card',
                        number_format(rand(1000, 3000), 2),
                        number_format(rand(500, 1500), 2),
                        number_format(rand(500, 1500), 2),
                        Carbon::now()->addDays(30)->format('F j, Y'),
                    ],
                    $template['body']
                );

                $subject = str_replace(
                    ['{patient_name}', '{time}'],
                    [$patient->user->name, '10:30 AM'],
                    $template['subject']
                );

                $sentAt = null;
                if ($template['status'] === 'sent' && $template['days_ago']) {
                    $sentAt = Carbon::now()->subDays($template['days_ago']);
                }

                // Randomly assign a doctor who sent the email (70% doctor, 30% system/null)
                $sentByUserId = null;
                if (!$doctors->isEmpty() && rand(1, 10) > 3) {
                    $randomDoctor = $doctors->random();
                    $sentByUserId = $randomDoctor->getAttributeValue('user_id');
                }

                Email::create([
                    'patient_id' => $patient->id,
                    'company_id' => 2,
                    'sent_by_user_id' => $sentByUserId,
                    'recipient' => $patient->user->email,
                    'subject' => $subject,
                    'body' => $body,
                    'status' => $template['status'],
                    'sent_at' => $sentAt,
                    'created_at' => $sentAt ?? now(),
                    'updated_at' => $sentAt ?? now(),
                ]);

                $createdCount++;
            }
        }

        $this->command->info("Created {$createdCount} realistic email records for {$patients->count()} patients.");
    }
}
