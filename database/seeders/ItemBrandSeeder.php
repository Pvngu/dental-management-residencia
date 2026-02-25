<?php

namespace Database\Seeders;

use App\Models\ItemBrand;
use App\Models\Company;
use Illuminate\Database\Seeder;

class ItemBrandSeeder extends Seeder
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

        $brands = [
            [
                'name' => '3M ESPE',
                'description' => 'Marca líder en materiales dentales y productos de restauración',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Dentsply Sirona',
                'description' => 'Fabricante global de productos dentales profesionales',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Ivoclar Vivadent',
                'description' => 'Especialista en materiales dentales innovadores',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Kerr',
                'description' => 'Productos dentales de alta calidad para restauración',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'GC Corporation',
                'description' => 'Innovación en materiales dentales desde Japón',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Ultradent',
                'description' => 'Productos dentales especializados en blanqueamiento y endodoncia',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Septodont',
                'description' => 'Especialista en anestésicos dentales',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Oral-B',
                'description' => 'Productos de higiene oral profesional',
                'is_active' => true,
                'company_id' => $company->id,
            ]
        ];

        foreach ($brands as $brand) {
            ItemBrand::create($brand);
        }

        $this->command->info('¡Marcas de productos creadas correctamente!');
    }
}