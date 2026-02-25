<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AddSellerRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all companies (excluding global)
        $companies = Company::where('is_global', 0)->get();

        foreach ($companies as $company) {
            // Set the company context for permissions
            app(PermissionRegistrar::class)->setPermissionsTeamId($company->id);

            // Create seller role if it doesn't exist
            if (!Role::where('name', 'seller')->where('company_id', $company->id)->exists()) {
                Role::create([
                    'name' => 'seller',
                    'company_id' => $company->id,
                    'display_name' => 'Seller',
                    'description' => 'Primary role for sellers.',
                    'is_system' => true,
                ]);
                
                $this->command->info("✅ Seller role created for company: {$company->name}");
            } else {
                $this->command->info("ℹ️  Seller role already exists for company: {$company->name}");
            }
        }

        $this->command->info('🎉 Seller role seeding completed!');
    }
}
