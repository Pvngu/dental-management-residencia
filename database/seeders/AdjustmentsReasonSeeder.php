<?php

namespace Database\Seeders;

use App\Models\AdjustmentsReason;
use App\Models\Company;
use Illuminate\Database\Seeder;

class AdjustmentsReasonSeeder extends Seeder
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

        $reasons = [
            [
                'name' => 'Producto Vencido',
                'description' => 'Ajuste por productos que han llegado a su fecha de vencimiento',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Producto Dañado',
                'description' => 'Ajuste por productos que se han dañado durante el almacenamiento o manejo',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Error en Conteo',
                'description' => 'Corrección de inventario debido a errores en el conteo físico',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Muestra Médica',
                'description' => 'Productos utilizados como muestras para pacientes',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Capacitación',
                'description' => 'Productos utilizados para entrenamiento del personal',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Robo o Pérdida',
                'description' => 'Ajuste por productos perdidos o sustraídos',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Devolución a Proveedor',
                'description' => 'Productos devueltos al proveedor por defectos o garantía',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Ajuste de Entrada',
                'description' => 'Incremento de inventario por entrada no registrada anteriormente',
                'is_active' => true,
                'company_id' => $company->id,
            ]
        ];

        foreach ($reasons as $reason) {
            AdjustmentsReason::create($reason);
        }

        $this->command->info('¡Razones de ajuste de inventario creadas correctamente!');
    }
}