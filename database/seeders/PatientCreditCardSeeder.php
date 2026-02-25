<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PatientCreditCard;
use App\Models\Patient;

class PatientCreditCardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing patients
        $patients = Patient::all();

        foreach ($patients as $patient) {
            // Create 1-3 credit cards per patient
            $cardCount = rand(1, 3);
            
            for ($i = 0; $i < $cardCount; $i++) {
                PatientCreditCard::factory()->create([
                    'patient_id' => $patient->id,
                    'company_id' => $patient->company_id,
                    'is_default' => $i === 0, // First card is default
                ]);
            }
        }
    }
}
