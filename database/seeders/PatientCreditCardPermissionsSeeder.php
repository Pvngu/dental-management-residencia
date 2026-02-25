<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PatientCreditCardPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'patient_credit_cards_view',
            'patient_credit_cards_create', 
            'patient_credit_cards_edit',
            'patient_credit_cards_delete'
        ];

        foreach ($permissions as $permission) {
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
                $this->command->info('Created permission: ' . $permission);
            } else {
                $this->command->info('Permission already exists: ' . $permission);
            }
        }

        $this->command->info('Patient credit cards permissions created successfully!');
    }
}
