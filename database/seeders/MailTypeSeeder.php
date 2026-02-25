<?php

namespace Database\Seeders;

use App\Models\MailType;
use App\Models\Company;
use Illuminate\Database\Seeder;

class MailTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();

        if (!$company) {
            $this->command->error('No company found. Please run CompanyTableSeeder first.');
            return;
        }

        // Mail types pulled from UI mock: Document, Package, Legal Notice, Invoice, Lab Results, Insurance Claim, Patient Records, Prescription
        $types = [
            ['name' => 'Document', 'description' => 'General documents', 'is_active' => true, 'company_id' => $company->id],
            ['name' => 'Package', 'description' => 'Parcels and packages', 'is_active' => true, 'company_id' => $company->id],
            ['name' => 'Legal Notice', 'description' => 'Official legal notices', 'is_active' => true, 'company_id' => $company->id],
            ['name' => 'Invoice', 'description' => 'Billing and invoice documents', 'is_active' => true, 'company_id' => $company->id],
            ['name' => 'Lab Results', 'description' => 'Laboratory results and reports', 'is_active' => true, 'company_id' => $company->id],
            ['name' => 'Insurance Claim', 'description' => 'Insurance claim documents', 'is_active' => true, 'company_id' => $company->id],
            ['name' => 'Patient Records', 'description' => 'Medical and patient records', 'is_active' => true, 'company_id' => $company->id],
            ['name' => 'Prescription', 'description' => 'Prescription documents', 'is_active' => true, 'company_id' => $company->id],
        ];

        foreach ($types as $type) {
            MailType::updateOrCreate(
                ['company_id' => $company->id, 'name' => $type['name']],
                $type
            );
        }

        $this->command->info('Mail types seeded successfully.');
    }
}
