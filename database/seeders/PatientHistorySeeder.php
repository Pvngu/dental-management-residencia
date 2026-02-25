<?php

namespace Database\Seeders;

use App\Models\PatientHistory;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some patients and users for testing
        $patients = Patient::limit(5)->get();
        $users = User::limit(3)->get();

        if ($patients->isEmpty() || $users->isEmpty()) {
            $this->command->warn('No patients or users found. Please seed patients and users first.');
            return;
        }

        $eventTypes = ['appointment', 'treatment', 'payment', 'note', 'document'];

        foreach ($patients as $patient) {
            // Create 3-5 history entries per patient
            $historyCount = rand(3, 5);
            
            for ($i = 0; $i < $historyCount; $i++) {
                $eventType = $eventTypes[array_rand($eventTypes)];
                $user = $users->random();
                
                $data = $this->getEventData($eventType);
                
                PatientHistory::create([
                    'patient_id' => $patient->id,
                    'user_id' => $user->id,
                    'event_type' => $eventType,
                    'data' => $data,
                    'created_at' => now()->subDays(rand(1, 30))->subHours(rand(1, 23)),
                    'company_id' => $patient->company_id ?? 1,
                ]);
            }
        }

        $this->command->info('Patient history entries created successfully!');
    }

    private function getEventData($eventType)
    {
        switch ($eventType) {
            case 'appointment':
                return [
                    'title' => 'Dental Checkup',
                    'description' => 'Regular dental examination and cleaning',
                    'appointment_date' => now()->addDays(rand(1, 30))->format('Y-m-d H:i:s'),
                    'duration' => rand(30, 120),
                    'treatment_type' => 'Dental Cleaning',
                    'status' => collect(['scheduled', 'completed', 'cancelled'])->random(),
                ];

            case 'treatment':
                return [
                    'title' => 'Dental Treatment',
                    'description' => 'Dental treatment performed',
                    'treatment_name' => collect(['Teeth Cleaning', 'Cavity Filling', 'Root Canal', 'Tooth Extraction'])->random(),
                    'treatment_details' => 'Treatment completed successfully with no complications.',
                ];

            case 'payment':
                return [
                    'title' => 'Payment Received',
                    'description' => 'Payment for dental services',
                    'amount' => rand(50, 500),
                    'payment_method' => collect(['cash', 'credit_card', 'debit_card', 'insurance'])->random(),
                ];

            case 'note':
                return [
                    'title' => 'Patient Note',
                    'description' => 'Important note about patient care or condition',
                ];

            case 'document':
                return [
                    'title' => 'Document Upload',
                    'description' => 'Document uploaded to patient file',
                    'document_name' => collect(['X-Ray', 'Insurance Card', 'Medical History', 'Treatment Plan'])->random(),
                ];

            default:
                return [
                    'title' => 'General Activity',
                    'description' => 'General patient activity',
                ];
        }
    }
}
