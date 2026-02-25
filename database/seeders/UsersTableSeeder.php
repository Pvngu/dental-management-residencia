<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ClinicLocation;
use App\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Scopes\CompanyScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        Model::unguard();

        // DB::table('users')->delete();
        // Using model_has_roles instead of role_user for Laravel Permission package
        DB::table('model_has_roles')->delete();

        DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');

        $count = env('SEED_RECORD_COUNT', 30);
        $faker = \Faker\Factory::create();

        $company = Company::where('is_global', 0)->first();
        $clinic = ClinicLocation::where('company_id', $company->id)->first(); // Use first clinic for main users

        // Admin User (Access to ALL clinics)
        $adminRole = Role::withoutGlobalScope(CompanyScope::class)
            ->where('name', 'admin')->first();
        $admin = new User();
        $admin->company_id = $company->id;
        $admin->name = 'Admin';
        $admin->email = 'admin@example.com';
        $admin->password = '12345678';
        $admin->role_id = $adminRole->id;
        $admin->user_type = "staff_members";
        $admin->role_type = 'staff';
        $admin->default_clinic_id = $clinic ? $clinic->id : null;
        $admin->save();
        
        // Attach Admin to ALL clinics of the company
        $allClinics = ClinicLocation::where('company_id', $company->id)->pluck('id');
        $syncDataAdmin = [];
        foreach ($allClinics as $clinicId) {
            $syncDataAdmin[$clinicId] = ['role_id' => $adminRole->id];
        }
        $admin->clinics()->sync($syncDataAdmin);

        // Set company context and assign role
        setPermissionsTeamId($company->id);
        $admin->assignRole($adminRole);

        $company->admin_id = $admin->id;
        $company->save();

        // Doctor User (Access to ALL clinics for demo)
        $doctorRole = Role::withoutGlobalScope(CompanyScope::class)
            ->where('name', 'doctor')->first();
        $doctor = new User();
        $doctor->company_id = $company->id;
        $doctor->name = 'Doctor';
        $doctor->email = 'doctor@example.com';
        $doctor->password = '12345678';
        $doctor->role_id = $doctorRole->id;
        $doctor->user_type = "staff_members";
        $doctor->role_type = 'doctor';
        $doctor->default_clinic_id = $clinic ? $clinic->id : null;
        $doctor->save();
        $doctor->save();
        
        $syncDataDoctor = [];
        foreach ($allClinics as $clinicId) {
             $syncDataDoctor[$clinicId] = ['role_id' => $doctorRole->id];
        }
        $doctor->clinics()->sync($syncDataDoctor); // Demo doctor sees all
        $doctor->assignRole($doctorRole);

        // Patient User (Access only to default clinic)
        $patientRole = Role::withoutGlobalScope(CompanyScope::class)
            ->where('name', 'patient')->first();
        $patient = new User();
        $patient->company_id = $company->id;
        $patient->name = 'Patient';
        $patient->email = 'patient@example.com';
        $patient->password = '12345678';
        $patient->role_id = $patientRole->id;
        $patient->user_type = "staff_members";
        $patient->role_type = 'patient';
        $patient->default_clinic_id = $clinic ? $clinic->id : null;
        $patient->save();
        // if ($clinic) {
        //     $patient->clinics()->attach($clinic->id);
        // }
        $patient->assignRole($patientRole);

        $allRoles = [
            $adminRole->id,
            $doctorRole->id,
            $patientRole->id,
        ];

        // StaffMembers
        User::factory()->count((int)$count)->create([
            'company_id' => $company->id
        ])->each(function ($user) use ($faker, $allRoles, $company) {
            
            // Random clinic for staff
            $randomClinic = ClinicLocation::where('company_id', $company->id)->inRandomOrder()->first();
            $user->default_clinic_id = $randomClinic ? $randomClinic->id : null;

            $roleId = $faker->randomElement($allRoles);

            $user->role_id = $roleId;
            $user->role_type = 'staff';
            $user->save();
            
            // Attach to the assigned clinic
            if ($randomClinic) {
                $user->clinics()->attach($randomClinic->id, ['role_id' => $roleId]);
            }

            // Assign Role
            $role = Role::find($roleId);
            if ($role) {
                $user->assignRole($role);
            }
        });
    }
}
