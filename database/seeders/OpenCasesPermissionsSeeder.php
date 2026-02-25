<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class OpenCasesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'open_cases_view',
            'open_cases_create', 
            'open_cases_edit',
            'open_cases_delete'
        ];

        foreach ($permissions as $permission) {
            if (!Permission::where('name', $permission)->exists()) {
                Permission::create(['name' => $permission]);
                $this->command->info('Created permission: ' . $permission);
            } else {
                $this->command->info('Permission already exists: ' . $permission);
            }
        }

        $this->command->info('Open cases permissions created successfully!');
    }
}
