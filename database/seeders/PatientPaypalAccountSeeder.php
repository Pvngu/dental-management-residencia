<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PatientPaypalAccount;
use App\Models\Patient;

class PatientPaypalAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing patients
        $patients = Patient::all();

        foreach ($patients as $patient) {
            // Create 0-1 PayPal account per patient (not all patients will have PayPal)
            $hasPaypal = rand(0, 1); // 50% chance of having a PayPal account
            
            if ($hasPaypal) {
                PatientPaypalAccount::factory()->create([
                    'patient_id' => $patient->id,
                    'company_id' => $patient->company_id,
                    'is_default' => true, // PayPal is default if it's the only one
                ]);
            }
        }
    }
}
