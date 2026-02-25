<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class ReceptionistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating receptionists...');

        $companies = Company::all();
        
        // Sample receptionist data
        $receptionists = [
            [
                'name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@example.com',
                'phone' => '+1234567890',
                'gender' => 'female',
                'status' => 'active'
            ],
            [
                'name' => 'Emily',
                'last_name' => 'Davis',
                'email' => 'emily.davis@example.com',
                'phone' => '+1234567891',
                'gender' => 'female',
                'status' => 'active'
            ],
            [
                'name' => 'Michael',
                'last_name' => 'Brown',
                'email' => 'michael.brown@example.com',
                'phone' => '+1234567892',
                'gender' => 'male',
                'status' => 'active'
            ],
            [
                'name' => 'Jessica',
                'last_name' => 'Wilson',
                'email' => 'jessica.wilson@example.com',
                'phone' => '+1234567893',
                'gender' => 'female',
                'status' => 'active'
            ],
            [
                'name' => 'David',
                'last_name' => 'Martinez',
                'email' => 'david.martinez@example.com',
                'phone' => '+1234567894',
                'gender' => 'male',
                'status' => 'inactive'
            ]
        ];

        foreach ($companies as $company) {
            // Get the receptionist role for this company
            $receptionistRole = Role::where('company_id', $company->id)
                ->where('name', 'receptionist')
                ->first();

            if (!$receptionistRole) {
                // If receptionist role doesn't exist, skip this company
                $this->command->warn("Receptionist role not found for company: {$company->name}");
                continue;
            }

            // Create 2-3 receptionists per company
            $numReceptionists = rand(2, 3);
            
            for ($i = 0; $i < $numReceptionists && $i < count($receptionists); $i++) {
                $receptionistData = $receptionists[$i];
                
                // Check if user already exists for this company
                $existingUser = User::where('email', $receptionistData['email'])
                    ->where('company_id', $company->id)
                    ->first();

                if (!$existingUser) {
                    $user = User::create([
                        'company_id' => $company->id,
                        'role_id' => $receptionistRole ? $receptionistRole->id : null,
                        'name' => $receptionistData['name'],
                        'last_name' => $receptionistData['last_name'],
                        'email' => $receptionistData['email'],
                        'password' => '12345678',
                        'phone' => $receptionistData['phone'],
                        'country_code' => '+1',
                        'gender' => $receptionistData['gender'],
                        'user_type' => 'staff_members',
                        'role_type' => 'receptionist',
                        'status' => $receptionistData['status'],
                        'language' => 'en',
                        'login_enabled' => 1,
                        'date_of_birth' => now()->subYears(rand(22, 45))->format('Y-m-d'),
                        'created_at' => now()->subDays(rand(1, 30)),
                        'updated_at' => now()->subDays(rand(0, 10)),
                    ]);

                    $this->command->info("Created receptionist: {$user->name} {$user->last_name} for company: {$company->name}");
                }
            }
        }

        $this->command->info('Receptionists seeded successfully!');
    }
}