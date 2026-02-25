<?php

namespace Database\Seeders;

use App\Models\DoctorDepartment;
use App\Models\Company;
use Illuminate\Database\Seeder;

class DoctorDepartmentSeeder extends Seeder
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

        $departments = [
            [
                'name' => 'Odontología General',
                'description' => 'Departamento de servicios dentales generales y preventivos',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Especialidades Dentales',
                'description' => 'Departamento de especialidades odontológicas avanzadas',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Cirugía Oral',
                'description' => 'Departamento de procedimientos quirúrgicos orales y maxilofaciales',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Estética Dental',
                'description' => 'Departamento de tratamientos estéticos y cosméticos dentales',
                'company_id' => $company->id,
            ],
            [
                'name' => 'Pediatría Dental',
                'description' => 'Departamento especializado en atención dental infantil',
                'company_id' => $company->id,
            ],
        ];

        foreach ($departments as $department) {
            DoctorDepartment::create($department);
        }

        $this->command->info('¡Departamentos médicos creados correctamente!');
    }
}