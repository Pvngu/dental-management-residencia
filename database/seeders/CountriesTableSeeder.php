<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            [
                'name' => 'México',
                'code' => 'MX',
                'phone_code' => '+52',
                'currency_code' => 'MXN',
                'currency_symbol' => '$',
                'language' => 'es',
                'status' => 1,
            ],
            [
                'name' => 'United States',
                'code' => 'US',
                'phone_code' => '+1',
                'currency_code' => 'USD',
                'currency_symbol' => '$',
                'language' => 'en',
                'status' => 1,
            ],
            [
                'name' => 'Canada',
                'code' => 'CA',
                'phone_code' => '+1',
                'currency_code' => 'CAD',
                'currency_symbol' => '$',
                'language' => 'en',
                'status' => 1,
            ],
        ];

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['code' => $country['code']], // Find by code
                $country // Update or create with all data
            );
        }
    }
}
