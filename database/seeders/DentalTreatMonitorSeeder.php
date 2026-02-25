<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DentalTreatMonitor;
use App\Models\Patient;
use App\Models\User;

class DentalTreatMonitorSeeder extends Seeder
{
    public function run()
    {
        // Get first patient and user for testing
        $patient = Patient::first();
        $user = User::first();

        if ($patient && $user) {
            $items = [
                [
                    'patient_id' => $patient->id,
                    'tooth_number' => '15',
                    'type' => 'urgent',
                    'status' => 'active',
                    'content' => '15, Crown, Ceramic, Abutment, Implant, Tissue level',
                    'comment' => 'Patient reports sensitivity when eating cold foods',
                    'created_by' => $user->id,
                    'company_id' => $patient->company_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'patient_id' => $patient->id,
                    'tooth_number' => '15',
                    'type' => 'important',
                    'status' => 'active',
                    'content' => '15, Buccal, Veneer, Composite, Sufficient, Overhang',
                    'comment' => null,
                    'created_by' => $user->id,
                    'company_id' => $patient->company_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'patient_id' => $patient->id,
                    'tooth_number' => '15',
                    'type' => 'normal',
                    'status' => 'active',
                    'content' => '15, Mesial, Occlusal, Distal, Inlay, Gold, Sufficient, Flush',
                    'comment' => null,
                    'created_by' => $user->id,
                    'company_id' => $patient->company_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'patient_id' => $patient->id,
                    'tooth_number' => '15',
                    'type' => 'normal',
                    'status' => 'resolved',
                    'content' => '15, Discoloration, Gray',
                    'comment' => 'Whitening treatment completed successfully',
                    'created_by' => $user->id,
                    'resolved_by' => $user->id,
                    'resolved_at' => now()->subDays(2),
                    'company_id' => $patient->company_id,
                    'created_at' => now()->subDays(7),
                    'updated_at' => now()->subDays(2),
                ]
            ];

            foreach ($items as $item) {
                DentalTreatMonitor::create($item);
            }

            $this->command->info('Created 4 dental treat monitor items for patient: ' . $patient->first_name . ' ' . $patient->last_name);
        } else {
            $this->command->error('No patients or users found. Please seed patients and users first.');
        }
    }
}
