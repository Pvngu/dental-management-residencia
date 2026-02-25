<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Classes\PermsSeed;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Seeding Doctor Role Permissions
        PermsSeed::seedDoctorRolePermissions();
        
        // Seeding Receptionist Role Permissions
        PermsSeed::seedReceptionistRolePermissions();
    }
}
