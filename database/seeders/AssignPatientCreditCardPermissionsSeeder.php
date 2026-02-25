<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignPatientCreditCardPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Find admin role (try different possible names)
        $adminRole = Role::where('name', 'admin')
            ->orWhere('name', 'Admin')
            ->orWhere('name', 'Administrator')
            ->orWhere('id', 1)
            ->first();

        if (!$adminRole) {
            $this->command->error('Admin role not found!');
            return;
        }

        $this->command->info('Found admin role: ' . $adminRole->name . ' (ID: ' . $adminRole->id . ')');

        // Get patient credit card permissions
        $permissions = Permission::whereIn('name', [
            'patient_credit_cards_view',
            'patient_credit_cards_create',
            'patient_credit_cards_edit',
            'patient_credit_cards_delete'
        ])->get();

        if ($permissions->count() === 0) {
            $this->command->error('Patient credit card permissions not found!');
            return;
        }

        $this->command->info('Found ' . $permissions->count() . ' patient credit card permissions');

        // Assign permissions to admin role
        foreach ($permissions as $permission) {
            if (!$adminRole->hasPermissionTo($permission)) {
                $adminRole->givePermissionTo($permission);
                $this->command->info('✓ Assigned permission: ' . $permission->name);
            } else {
                $this->command->info('- Permission already assigned: ' . $permission->name);
            }
        }

        $this->command->info('Patient credit card permissions assigned to admin role successfully!');
    }
}
