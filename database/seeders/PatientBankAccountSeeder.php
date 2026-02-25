<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PatientBankAccount;
use App\Models\Patient;

class PatientBankAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing patients
        $patients = Patient::all();

        foreach ($patients as $patient) {
            // Create 0-2 bank accounts per patient (some patients may not have bank accounts)
            $accountCount = rand(0, 2);
            
            for ($i = 0; $i < $accountCount; $i++) {
                PatientBankAccount::factory()->create([
                    'patient_id' => $patient->id,
                    'company_id' => $patient->company_id,
                    'is_default' => $i === 0, // First account is default
                ]);
            }
        }
    }
}
