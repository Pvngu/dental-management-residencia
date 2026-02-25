<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\InsuranceProvider;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InsuranceProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();

        $insuranceProviders = [
            [
                'name' => 'Blue Cross Blue Shield',
                'payor_id' => 'BCBS001',
                'is_active' => true,
            ],
            [
                'name' => 'Aetna Health',
                'payor_id' => 'AET001',
                'is_active' => true,
            ],
            [
                'name' => 'United Healthcare',
                'payor_id' => 'UHC001',
                'is_active' => true,
            ],
            [
                'name' => 'Cigna Healthcare',
                'payor_id' => 'CIG001',
                'is_active' => true,
            ],
            [
                'name' => 'Humana Health Plan',
                'payor_id' => 'HUM001',
                'is_active' => true,
            ],
            [
                'name' => 'Medicare',
                'payor_id' => 'MED001',
                'is_active' => true,
            ],
            [
                'name' => 'Medicaid',
                'payor_id' => 'MCA001',
                'is_active' => true,
            ],
            [
                'name' => 'Anthem Blue Cross',
                'payor_id' => 'ABC001',
                'is_active' => true,
            ],
        ];

        foreach ($insuranceProviders as $provider) {
            InsuranceProvider::create([
                ...$provider,
                'company_id' => $company->id,
            ]);
        }

        $this->command->info('✅ Insurance providers seeded successfully!');
    }
}
