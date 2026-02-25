<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\ClinicLocation;
use App\Models\Lang;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        Model::unguard();

        DB::table('companies')->delete();

        DB::statement('ALTER TABLE companies AUTO_INCREMENT = 1');

        $faker = \Faker\Factory::create();

        $enLang = Lang::where('key', 'es')->first();

        $adminCompany = new Company();
        $adminCompany->name = '800dent';
        $adminCompany->short_name = '800dent';
        $adminCompany->email = 'company@example.com';
        $adminCompany->phone = $faker->e164PhoneNumber();
        $adminCompany->address = '7 street, city, state, 762782';
        $adminCompany->lang_id = $enLang->id;
        $adminCompany->enable_landing_page = true;
        $adminCompany->company_slug = '800dent';
        $adminCompany->is_global = true; // Set as global company for branding
        $adminCompany->max_clinic_locations = 10; // Allow multiple clinics for admin company
        $adminCompany->save();

        $company = new Company();
        $company->name = 'Default Company';
        $company->short_name = 'Default';
        $company->email = 'company@example.com';
        $company->phone = $faker->e164PhoneNumber();
        $company->address = '123 Main St, Anytown, USA';
        $company->lang_id = $enLang->id;
        $company->max_clinic_locations = 3; // Allow 3 clinic locations
        
        // Add sample Twilio phone numbers (credentials come from .env)
        $company->twilio_enabled = config('services.twilio.enabled', false);
        $company->twilio_phone_number = config('services.twilio.from');
        $company->twilio_whatsapp_number = config('services.twilio.whatsapp_from');
        
        $company->save();

        // Create Clinic Locations for the company
        $clinics = [
            [
                'name' => 'Main Branch',
                'phone_number' => $faker->e164PhoneNumber(),
                'email' => 'main@example.com',
                'address' => '123 Main St, Suite 100',
                // Main branch uses its own Twilio phone number
                'use_own_twilio' => true,
                'twilio_phone_number' => config('services.twilio.from'), // Uses same number from .env for demo
                'twilio_whatsapp_number' => config('services.twilio.whatsapp_from'),
            ],
            [
                'name' => 'Downtown Clinic',
                'phone_number' => $faker->e164PhoneNumber(),
                'email' => 'downtown@example.com',
                'address' => '456 Downtown Ave',
                // Downtown clinic can optionally override with its own phone number
                // Uncomment below to enable clinic-specific phone number
                'use_own_twilio' => false,
                // 'twilio_phone_number' => '+15551234567',
                // 'twilio_whatsapp_number' => 'whatsapp:+15551234567',
            ],
            [
                'name' => 'Westside Clinic',
                'phone_number' => $faker->e164PhoneNumber(),
                'email' => 'westside@example.com',
                'address' => '789 Westside Blvd',
                'use_own_twilio' => false,
            ],
        ];

        foreach ($clinics as $clinicData) {
            $clinic = new ClinicLocation();
            $clinic->company_id = $company->id;
            $clinic->name = $clinicData['name'];
            $clinic->phone_number = $clinicData['phone_number'];
            $clinic->email = $clinicData['email'];
            $clinic->status = true;
            $clinic->use_own_twilio = $clinicData['use_own_twilio'];
            
            // Set clinic-specific Twilio phone numbers if provided
            if (isset($clinicData['twilio_phone_number'])) {
                $clinic->twilio_phone_number = $clinicData['twilio_phone_number'];
                $clinic->twilio_whatsapp_number = $clinicData['twilio_whatsapp_number'] ?? null;
            }
            
            $clinic->save();
        }
    }
}
