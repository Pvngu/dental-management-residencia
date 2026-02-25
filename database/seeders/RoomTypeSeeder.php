<?php

namespace Database\Seeders;

use App\Models\RoomType;
use App\Models\Company;
use Illuminate\Database\Seeder;

class RoomTypeSeeder extends Seeder
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

        $roomTypes = [
            [
                'name' => 'Consultorio General',
                'description' => 'Consultorio para atención dental general, exámenes de rutina y procedimientos básicos',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Consultorio de Especialidades',
                'description' => 'Consultorio equipado para especialidades como ortodoncia, endodoncia y periodoncia',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Quirófano Dental',
                'description' => 'Sala de cirugía dental equipada para procedimientos quirúrgicos orales y maxilofaciales',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Sala de Radiología',
                'description' => 'Sala especializada para toma de radiografías dentales y estudios imagenológicos',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Sala de Higiene',
                'description' => 'Sala dedicada a profilaxis dental y tratamientos de higiene oral',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Laboratorio Dental',
                'description' => 'Espacio para trabajo de laboratorio dental, confección de prótesis y aparatos',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Sala de Esterilización',
                'description' => 'Área de esterilización y desinfección de instrumentos dentales',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Sala de Recuperación',
                'description' => 'Espacio para la recuperación post-operatoria de pacientes',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Consultorio Pediátrico',
                'description' => 'Consultorio especialmente diseñado para la atención dental infantil',
                'is_active' => true,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Sala de Urgencias',
                'description' => 'Sala equipada para atención de emergencias dentales',
                'is_active' => true,
                'company_id' => $company->id,
            ]
        ];

        foreach ($roomTypes as $roomType) {
            RoomType::create($roomType);
        }

        $this->command->info('¡Tipos de sala creados correctamente!');
    }
}