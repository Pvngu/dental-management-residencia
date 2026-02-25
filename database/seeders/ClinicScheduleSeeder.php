<?php

namespace Database\Seeders;

use App\Models\ClinicSchedule;
use App\Models\Company;
use Illuminate\Database\Seeder;

class ClinicScheduleSeeder extends Seeder
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

        $clinics = \App\Models\ClinicLocation::where('company_id', $company->id)->get();

        if ($clinics->isEmpty()) {
            $this->command->warn("No clinics found for company ID {$company->id}. Schedules might not be visible in multi-clinic context.");
        }

        foreach ($clinics as $clinic) {
            $clinicSchedules = [
                // Lunes (1)
                [
                    'company_id' => $company->id,
                    'clinic_id' => $clinic->id,
                    'day_of_week' => 1,
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00'
                ],
                // Martes (2)
                [
                    'company_id' => $company->id,
                    'clinic_id' => $clinic->id,
                    'day_of_week' => 2,
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00'
                ],
                // Miércoles (3)
                [
                    'company_id' => $company->id,
                    'clinic_id' => $clinic->id,
                    'day_of_week' => 3,
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00'
                ],
                // Jueves (4)
                [
                    'company_id' => $company->id,
                    'clinic_id' => $clinic->id,
                    'day_of_week' => 4,
                    'start_time' => '08:00:00',
                    'end_time' => '17:00:00'
                ],
                // Viernes (5)
                [
                    'company_id' => $company->id,
                    'clinic_id' => $clinic->id,
                    'day_of_week' => 5,
                    'start_time' => '08:00:00',
                    'end_time' => '16:00:00'
                ],
                // Sábado (6) - Medio día
                [
                    'company_id' => $company->id,
                    'clinic_id' => $clinic->id,
                    'day_of_week' => 6,
                    'start_time' => '09:00:00',
                    'end_time' => '13:00:00'
                ]
                // Domingo (7) - Cerrado
            ];

            foreach ($clinicSchedules as $schedule) {
                // Check if exists? Or just create. The unique index might error if we run twice.
                // Best practice: use firstOrCreate or updateOrCreate.
                ClinicSchedule::updateOrCreate(
                    [
                        'clinic_id' => $schedule['clinic_id'],
                        'day_of_week' => $schedule['day_of_week']
                    ],
                    $schedule
                );
            }
        }

        $this->command->info('¡Horarios de clínica creados correctamente!');
    }
}