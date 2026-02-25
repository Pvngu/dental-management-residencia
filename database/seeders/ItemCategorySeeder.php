<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use App\Models\Company;
use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
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

        $categories = [
            [
                'name' => 'Instrumentos Dentales',
                'description' => 'Instrumentos básicos para procedimientos dentales',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Materiales de Restauración',
                'description' => 'Materiales para empastes y restauraciones dentales',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Productos de Higiene',
                'description' => 'Productos para limpieza e higiene dental',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Equipos de Protección',
                'description' => 'Equipos de protección personal para el personal médico',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Anestésicos',
                'description' => 'Productos anestésicos y para manejo del dolor',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Material de Impresión',
                'description' => 'Materiales para toma de impresiones dentales',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Prótesis y Ortodoncia',
                'description' => 'Materiales y suministros para prótesis y ortodoncia',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Medicamentos',
                'description' => 'Medicamentos y productos farmacéuticos dentales',
                'is_active' => true,
                'company_id' => $company->id,
            ]
        ];

        foreach ($categories as $category) {
            ItemCategory::create($category);
        }

        $this->command->info('¡Categorías de productos creadas correctamente!');
    }
}