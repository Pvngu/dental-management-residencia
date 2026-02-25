<?php

namespace Database\Seeders;

use App\Models\TreatmentType;
use App\Models\Company;
use Illuminate\Database\Seeder;

class TreatmentTypeSeeder extends Seeder
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

        $treatmentTypes = [
            // PREVENTIVE - Preventivos
            [
                'name' => 'Consulta General',
                'description' => 'Examen dental de rutina y evaluación general de la salud oral',
                'duration_minutes' => 30,
                'price' => 500.00,
                'is_active' => true,
                'category' => 'Consultation',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Limpieza Dental (Profilaxis)',
                'description' => 'Limpieza profesional para remover placa y sarro dental',
                'duration_minutes' => 45,
                'price' => 800.00,
                'is_active' => true,
                'category' => 'Preventive',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Aplicación de Flúor',
                'description' => 'Tratamiento preventivo con flúor para fortalecer el esmalte dental',
                'duration_minutes' => 15,
                'price' => 200.00,
                'is_active' => true,
                'category' => 'Preventive',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Selladores de Fisuras',
                'description' => 'Protección preventiva de molares con material sellador',
                'duration_minutes' => 30,
                'price' => 350.00,
                'is_active' => true,
                'category' => 'Preventive',
                'company_id' => $company->id,
            ],

            // DIAGNOSTIC - Diagnósticos
            [
                'name' => 'Radiografía Dental',
                'description' => 'Estudio radiográfico para diagnóstico dental',
                'duration_minutes' => 15,
                'price' => 150.00,
                'is_active' => true,
                'category' => 'Diagnostic',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Radiografía Panorámica',
                'description' => 'Radiografía completa de ambos maxilares y estructuras adyacentes',
                'duration_minutes' => 20,
                'price' => 400.00,
                'is_active' => true,
                'category' => 'Diagnostic',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Tomografía 3D',
                'description' => 'Estudio tomográfico tridimensional para diagnóstico avanzado',
                'duration_minutes' => 25,
                'price' => 800.00,
                'is_active' => true,
                'category' => 'Diagnostic',
                'company_id' => $company->id,
            ],

            // RESTORATIVE - Restaurativos
            [
                'name' => 'Empaste de Resina',
                'description' => 'Restauración dental con resina compuesta',
                'duration_minutes' => 60,
                'price' => 600.00,
                'is_active' => true,
                'category' => 'Restorative',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Empaste de Amalgama',
                'description' => 'Restauración dental con amalgama dental',
                'duration_minutes' => 45,
                'price' => 450.00,
                'is_active' => true,
                'category' => 'Restorative',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Corona Dental',
                'description' => 'Prótesis fija para restaurar diente dañado',
                'duration_minutes' => 120,
                'price' => 2500.00,
                'is_active' => true,
                'category' => 'Restorative',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Incrustación (Inlay/Onlay)',
                'description' => 'Restauración parcial de diente con material cerámico',
                'duration_minutes' => 90,
                'price' => 1800.00,
                'is_active' => true,
                'category' => 'Restorative',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Carilla Dental',
                'description' => 'Lámina delgada para mejorar la apariencia dental',
                'duration_minutes' => 90,
                'price' => 3000.00,
                'is_active' => true,
                'category' => 'Restorative',
                'company_id' => $company->id,
            ],

            // SURGICAL - Quirúrgicos
            [
                'name' => 'Extracción Simple',
                'description' => 'Extracción dental sin complicaciones',
                'duration_minutes' => 30,
                'price' => 800.00,
                'is_active' => true,
                'category' => 'Surgical',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Extracción Quirúrgica',
                'description' => 'Extracción dental con procedimiento quirúrgico',
                'duration_minutes' => 60,
                'price' => 1500.00,
                'is_active' => true,
                'category' => 'Surgical',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Implante Dental',
                'description' => 'Colocación de implante dental de titanio',
                'duration_minutes' => 90,
                'price' => 8000.00,
                'is_active' => true,
                'category' => 'Surgical',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Cirugía de Encías',
                'description' => 'Tratamiento quirúrgico periodontal',
                'duration_minutes' => 75,
                'price' => 2000.00,
                'is_active' => true,
                'category' => 'Surgical',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Endodoncia (Tratamiento de Conducto)',
                'description' => 'Tratamiento del nervio dental infectado o dañado',
                'duration_minutes' => 90,
                'price' => 2500.00,
                'is_active' => true,
                'category' => 'Surgical',
                'company_id' => $company->id,
            ],

            // ORTHODONTIC - Ortodónticos
            [
                'name' => 'Consulta de Ortodoncia',
                'description' => 'Evaluación ortodóntica inicial y plan de tratamiento',
                'duration_minutes' => 45,
                'price' => 800.00,
                'is_active' => true,
                'category' => 'Orthodontic',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Colocación de Brackets',
                'description' => 'Instalación inicial de aparato ortodóntico fijo',
                'duration_minutes' => 120,
                'price' => 15000.00,
                'is_active' => true,
                'category' => 'Orthodontic',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Ajuste de Brackets',
                'description' => 'Control y ajuste mensual de aparato ortodóntico',
                'duration_minutes' => 30,
                'price' => 600.00,
                'is_active' => true,
                'category' => 'Orthodontic',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Retirada de Brackets',
                'description' => 'Remoción de aparato ortodóntico al finalizar tratamiento',
                'duration_minutes' => 60,
                'price' => 1000.00,
                'is_active' => true,
                'category' => 'Orthodontic',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Retenedores Ortodónticos',
                'description' => 'Aparatos de retención post-tratamiento ortodóntico',
                'duration_minutes' => 45,
                'price' => 2500.00,
                'is_active' => true,
                'category' => 'Orthodontic',
                'company_id' => $company->id,
            ],

            // CONSULTATION - Consultas Especializadas
            [
                'name' => 'Consulta de Especialidad',
                'description' => 'Consulta con especialista en área específica',
                'duration_minutes' => 45,
                'price' => 800.00,
                'is_active' => true,
                'category' => 'Consultation',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Segunda Opinión',
                'description' => 'Evaluación de caso para obtener segunda opinión médica',
                'duration_minutes' => 30,
                'price' => 600.00,
                'is_active' => true,
                'category' => 'Consultation',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Evaluación Pre-quirúrgica',
                'description' => 'Evaluación médica previa a procedimiento quirúrgico',
                'duration_minutes' => 30,
                'price' => 500.00,
                'is_active' => true,
                'category' => 'Consultation',
                'company_id' => $company->id,
            ],

            // TRATAMIENTOS ESTÉTICOS
            [
                'name' => 'Blanqueamiento Dental',
                'description' => 'Tratamiento para aclarar el color de los dientes',
                'duration_minutes' => 60,
                'price' => 2000.00,
                'is_active' => true,
                'category' => 'Restorative',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Diseño de Sonrisa',
                'description' => 'Planificación estética integral de la sonrisa',
                'duration_minutes' => 90,
                'price' => 1500.00,
                'is_active' => true,
                'category' => 'Consultation',
                'company_id' => $company->id,
            ],

            // URGENCIAS
            [
                'name' => 'Consulta de Urgencia',
                'description' => 'Atención inmediata por dolor o trauma dental',
                'duration_minutes' => 20,
                'price' => 800.00,
                'is_active' => true,
                'category' => 'Consultation',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Tratamiento de Dolor',
                'description' => 'Manejo inmediato del dolor dental agudo',
                'duration_minutes' => 30,
                'price' => 600.00,
                'is_active' => true,
                'category' => 'Consultation',
                'company_id' => $company->id,
            ]
        ];

        foreach ($treatmentTypes as $treatmentType) {
            TreatmentType::create($treatmentType);
        }

        $this->command->info('¡Tipos de tratamiento creados correctamente!');
    }
}