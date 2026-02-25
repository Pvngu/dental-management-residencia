<?php

namespace Database\Seeders;

use App\Models\DoctorSpecialty;
use App\Models\Company;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DoctorSpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener la primera empresa
        $company = Company::first();

        if (!$company) {
            $this->command->error('No se encontró ninguna compañía. Por favor ejecute el seeder de compañías primero.');
            return;
        }

        $specialties = [
            [
                'name' => 'Ortodoncia',
                'description' => 'Especialista en alineación dental y problemas de mordida',
                'status' => 1,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Endodoncia',
                'description' => 'Especialista en tratamiento de conductos y enfermedades de la pulpa dental',
                'status' => 1,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Periodoncia',
                'description' => 'Especialista en enfermedades de las encías y estructuras que soportan los dientes',
                'status' => 1,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Odontopediatría',
                'description' => 'Especialista en atención dental infantil',
                'status' => 1,
                'company_id' => $company->id,
            ],
            [
                'name' => 'Cirugía Maxilofacial',
                'description' => 'Especialista en cirugías y tratamientos complejos de la región facial',
                'status' => 1,
                'company_id' => $company->id,
            ],
        ];

        foreach ($specialties as $specialty) {
            DoctorSpecialty::create($specialty);
        }

        $this->command->info('¡Especialidades médicas creadas correctamente!');
    }
}
