<?php

namespace Database\Seeders;

use App\Models\Seller;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Scopes\CompanyScope;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SellersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();
        
        if (!$company) {
            $this->command->error('No company found. Please run the companies seeder first.');
            return;
        }

        $sellerRole = Role::withoutGlobalScope(CompanyScope::class)
            ->where('name', 'seller')
            ->where('company_id', $company->id)
            ->first();
            
        if (!$sellerRole) {
            $this->command->info('Seller role not found. Creating seller role...');
            
            $sellerRole = new Role();
            $sellerRole->name = 'seller';
            $sellerRole->display_name = 'Seller';
            $sellerRole->description = 'Seller role with basic permissions';
            $sellerRole->company_id = $company->id;
            $sellerRole->save();
        }

        // Set company context for role assignment
        setPermissionsTeamId($company->id);

        $sellers = [
            [
                'name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john.doe@800dent.com',
                'phone' => '555-0101',
                'gender' => 'male',
                'status' => 'enabled',
                'commission_rate' => '10%',
            ],
            [
                'name' => 'Jane',
                'last_name' => 'Smith',
                'email' => 'jane.smith@800dent.com',
                'phone' => '555-0102',
                'gender' => 'female',
                'status' => 'enabled',
                'commission_rate' => '12%',
            ],
            [
                'name' => 'Michael',
                'last_name' => 'Johnson',
                'email' => 'michael.johnson@800dent.com',
                'phone' => '555-0103',
                'gender' => 'male',
                'status' => 'enabled',
                'commission_rate' => '15%',
            ],
        ];

        foreach ($sellers as $sellerData) {
            // Check if user already exists
            $existingUser = User::withoutGlobalScope(CompanyScope::class)
                ->where('email', $sellerData['email'])
                ->where('company_id', $company->id)
                ->first();

            if ($existingUser) {
                $this->command->info('Seller ' . $sellerData['name'] . ' ' . $sellerData['last_name'] . ' already exists. Skipping...');
                continue;
            }

            // Create user
            $user = new User();
            $user->name = $sellerData['name'];
            $user->last_name = $sellerData['last_name'];
            $user->email = $sellerData['email'];
            $user->phone = $sellerData['phone'] ?? null;
            $user->gender = $sellerData['gender'];
            $user->status = $sellerData['status'];
            $user->password = Hash::make('password123');
            $user->user_type = 'sellers';
            $user->company_id = $company->id;
            $user->role_id = $sellerRole->id;
            $user->role_type = 'seller';
            $user->save();
            
            // Assign role
            $user->assignRole($sellerRole->name);

            // Create seller
            $seller = new Seller();
            $seller->company_id = $company->id;
            $seller->user_id = $user->id;
            $seller->commission_rate = $sellerData['commission_rate'];
            $seller->status = 'active';
            $seller->save();

            $this->command->info('Created seller: ' . $sellerData['name'] . ' ' . $sellerData['last_name']);
        }

        $this->command->info('Sellers seeded successfully!');
    }
}
