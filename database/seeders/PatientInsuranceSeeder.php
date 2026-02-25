<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Patient;
use App\Models\PatientInsurance;
use App\Models\InsuranceProvider;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PatientInsuranceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();
        $patients = Patient::where('company_id', $company->id)->get();
        $providers = InsuranceProvider::where('company_id', $company->id)->get();

        if ($patients->isEmpty() || $providers->isEmpty()) {
            $this->command->warn('No patients or insurance providers found. Skipping patient insurance seeding.');
            return;
        }

        $planTypes = ['HMO', 'PPO', 'POS', 'EPO', 'Catastrophic', 'High Deductible'];
        $relationships = ['self', 'spouse', 'child', 'other'];
        
        $faker = \Faker\Factory::create();

        $count = 0;

        foreach ($patients as $patient) {
            // Randomly assign 1-2 insurance providers to each patient
            $numInsurances = rand(1, 2);
            $selectedProviders = $providers->random(min($numInsurances, $providers->count()));

            foreach ($selectedProviders as $index => $provider) {
                PatientInsurance::create([
                    'patient_id' => $patient->id,
                    'provider_id' => $provider->id,
                    'company_id' => $company->id,
                    'policy_holder_name' => $index === 0 ? $patient->user->name . ' ' . $patient->user->last_name : $faker->name(),
                    'relationship_to_holder' => $relationships[array_rand($relationships)],
                    'member_id' => 'MEM' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT),
                    'group_number' => 'GRP' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT),
                    'plan_type' => $planTypes[array_rand($planTypes)],
                    'is_primary' => $index === 0, // First insurance is primary
                    'verified_at' => now(),
                    'is_active' => true,
                ]);

                $count++;
            }
        }

        $this->command->info("✅ Patient insurances seeded successfully! ($count records created)");
    }
}
