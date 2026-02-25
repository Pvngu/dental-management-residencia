<?php

namespace Database\Seeders;

use App\Models\ItemManufacture;
use App\Models\Company;
use Illuminate\Database\Seeder;

class ItemManufactureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::first();

        if (!$company) {
            $this->command->error('No se encontró ninguna compañía. Por favor ejecute el seeder de compañías primero.');
            return;
        }

        $manufacturers = [
            [
                'name' => '3M Company',
                'description' => 'Multinacional estadounidense de tecnología diversificada',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Dentsply Sirona Inc.',
                'description' => 'Fabricante líder mundial de productos dentales profesionales',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Ivoclar Vivadent AG',
                'description' => 'Empresa suiza líder en materiales dentales innovadores',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Kerr Corporation',
                'description' => 'División dental de KaVo Kerr Group',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'GC Corporation',
                'description' => 'Empresa japonesa de materiales dentales',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Ultradent Products Inc.',
                'description' => 'Fabricante estadounidense de productos dentales especializados',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Septodont',
                'description' => 'Empresa francesa especializada en anestésicos dentales',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Procter & Gamble',
                'description' => 'Multinacional de productos de consumo (Oral-B)',
                'is_active' => true,
                'company_id' => $company->id,
            ]
        ];

        foreach ($manufacturers as $manufacturer) {
            ItemManufacture::create($manufacturer);
        }

        $this->command->info('¡Fabricantes de productos creados correctamente!');
    }
}