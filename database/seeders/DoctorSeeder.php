<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\DoctorDepartment;
use App\Models\DoctorSpecialty;
use App\Models\DoctorSchedule;
use App\Models\DoctorScheduleDay;
use App\Models\DoctorHoliday;
use App\Models\DoctorBreak;
use App\Models\User;
use App\Models\Company;
use App\Models\Role;
use App\Scopes\CompanyScope;
use Illuminate\Database\Seeder;

class DoctorSeeder extends Seeder
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

        $doctorRole = Role::withoutGlobalScope(CompanyScope::class)
            ->where('name', 'doctor')->first();
            
        if (!$doctorRole) {
            $this->command->error('No se encontró el rol de doctor. Por favor ejecute el seeder de roles primero.');
            return;
        }

        $departments = DoctorDepartment::all();
        $specialties = DoctorSpecialty::all();
        
        if ($departments->isEmpty()) {
            $this->command->error('No se encontraron departamentos médicos. Por favor ejecute el seeder de departamentos primero.');
            return;
        }

        // Set company context for role assignment
        setPermissionsTeamId($company->id);

        $doctors = [
            [
                'name' => 'Dr. María González',
                'email' => 'maria.gonzalez@800dent.com',
                'phone' => '+525551234567',
                'country_code' => 'MX',
                'qualification' => 'DDS, Especialista en Ortodoncia',
                'specialist' => 'Ortodoncia',
                'designation' => 'Ortodoncista Senior',
                'description' => 'Especialista en ortodoncia con más de 10 años de experiencia en tratamientos de alineación dental.',
                'practice_id' => 'DGO001',
                'appointment_charge' => 150.00,
                'missed_appointment_charge' => 75.00,
                'cancellation_notice_hours' => 24,
                'department' => 'Especialidades Dentales',
                'specialties' => ['Ortodoncia']
            ],
            [
                'name' => 'Dr. Carlos Rodríguez',
                'email' => 'carlos.rodriguez@800dent.com',
                'phone' => '+14155551234',
                'country_code' => 'US',
                'qualification' => 'DDS, Especialista en Endodoncia',
                'specialist' => 'Endodoncia',
                'designation' => 'Endodoncista',
                'description' => 'Experto en tratamientos de conductos radiculares y terapia pulpar.',
                'practice_id' => 'DRO002',
                'appointment_charge' => 180.00,
                'missed_appointment_charge' => 90.00,
                'cancellation_notice_hours' => 48,
                'department' => 'Especialidades Dentales',
                'specialties' => ['Endodoncia']
            ],
            [
                'name' => 'Dra. Ana Martínez',
                'email' => 'ana.martinez@800dent.com',
                'phone' => '+525552345678',
                'country_code' => 'MX',
                'qualification' => 'DDS, Especialista en Periodoncia',
                'specialist' => 'Periodoncia',
                'designation' => 'Periodoncista',
                'description' => 'Especialista en el tratamiento de enfermedades de las encías y tejidos de soporte dental.',
                'practice_id' => 'DMA003',
                'appointment_charge' => 160.00,
                'missed_appointment_charge' => 80.00,
                'cancellation_notice_hours' => 24,
                'department' => 'Especialidades Dentales',
                'specialties' => ['Periodoncia']
            ],
            [
                'name' => 'Dr. Luis Hernández',
                'email' => 'luis.hernandez@800dent.com',
                'phone' => '+13105556789',
                'country_code' => 'US',
                'qualification' => 'DDS, Especialista en Odontopediatría',
                'specialist' => 'Odontopediatría',
                'designation' => 'Odontopediatra',
                'description' => 'Especialista en atención dental infantil con enfoque en prevención y tratamiento temprano.',
                'practice_id' => 'DHE004',
                'appointment_charge' => 120.00,
                'missed_appointment_charge' => 60.00,
                'cancellation_notice_hours' => 24,
                'department' => 'Pediatría Dental',
                'specialties' => ['Odontopediatría']
            ],
            [
                'name' => 'Dr. Roberto Silva',
                'email' => 'roberto.silva@800dent.com',
                'phone' => '+525553456789',
                'country_code' => 'MX',
                'qualification' => 'DDS, MD, Especialista en Cirugía Maxilofacial',
                'specialist' => 'Cirugía Maxilofacial',
                'designation' => 'Cirujano Maxilofacial',
                'description' => 'Cirujano especializado en procedimientos complejos de la región oral y maxilofacial.',
                'practice_id' => 'DSI005',
                'appointment_charge' => 250.00,
                'missed_appointment_charge' => 125.00,
                'cancellation_notice_hours' => 72,
                'department' => 'Cirugía Oral',
                'specialties' => ['Cirugía Maxilofacial']
            ],
            [
                'name' => 'Dra. Elena López',
                'email' => 'elena.lopez@800dent.com',
                'phone' => '+12125557890',
                'country_code' => 'US',
                'qualification' => 'DDS, Especialista en Estética Dental',
                'specialist' => 'Estética Dental',
                'designation' => 'Especialista en Estética',
                'description' => 'Especialista en tratamientos estéticos y cosméticos dentales, incluyendo carillas y blanqueamiento.',
                'practice_id' => 'DLO006',
                'appointment_charge' => 200.00,
                'missed_appointment_charge' => 100.00,
                'cancellation_notice_hours' => 48,
                'department' => 'Estética Dental',
                'specialties' => ['Ortodoncia'] // Usando especialidad existente
            ],
            [
                'name' => 'Dr. Miguel Torres',
                'email' => 'miguel.torres@800dent.com',
                'phone' => '+525554567890',
                'country_code' => 'MX',
                'qualification' => 'DDS',
                'specialist' => 'Odontología General',
                'designation' => 'Dentista General',
                'description' => 'Dentista general con experiencia en tratamientos preventivos y restaurativos.',
                'practice_id' => 'DTO007',
                'appointment_charge' => 100.00,
                'missed_appointment_charge' => 50.00,
                'cancellation_notice_hours' => 24,
                'department' => 'Odontología General',
                'specialties' => []
            ],
            [
                'name' => 'Dra. Patricia Ruiz',
                'email' => 'patricia.ruiz@800dent.com',
                'phone' => '+13235558901',
                'country_code' => 'US',
                'qualification' => 'DDS',
                'specialist' => 'Odontología General',
                'designation' => 'Dentista General',
                'description' => 'Dentista con enfoque en tratamientos preventivos y educación en salud oral.',
                'practice_id' => 'DRU008',
                'appointment_charge' => 100.00,
                'missed_appointment_charge' => 50.00,
                'cancellation_notice_hours' => 24,
                'department' => 'Odontología General',
                'specialties' => []
            ]
        ];

        foreach ($doctors as $doctorData) {
            // Crear usuario para el doctor
            $user = new User();
            $user->company_id = $company->id;
            $user->name = $doctorData['name'];
            $user->email = $doctorData['email'];
            $user->phone = $doctorData['phone'] ?? null;
            $user->country_code = $doctorData['country_code'] ?? 'MX';
            $user->password = '12345678'; // Password por defecto
            $user->role_id = $doctorRole->id;
            $user->user_type = "staff_members";
            $user->role_type = 'doctor';
            $user->save();
            
            // Asignar rol
            $user->assignRole($doctorRole);

            // Buscar el departamento
            $department = $departments->where('name', $doctorData['department'])->first();
            
            if (!$department) {
                $this->command->warn("Departamento '{$doctorData['department']}' no encontrado para {$doctorData['name']}");
                continue;
            }

            // Crear doctor
            $doctor = new Doctor();
            $doctor->company_id = $company->id;
            $doctor->user_id = $user->id;
            $doctor->doctor_department_id = $department->id;
            $doctor->qualification = $doctorData['qualification'];
            $doctor->specialist = $doctorData['specialist'];
            $doctor->designation = $doctorData['designation'];
            $doctor->description = $doctorData['description'];
            $doctor->practice_id = $doctorData['practice_id'];
            $doctor->appointment_charge = $doctorData['appointment_charge'];
            $doctor->missed_appointment_charge = $doctorData['missed_appointment_charge'];
            $doctor->cancellation_notice_hours = $doctorData['cancellation_notice_hours'];
            $doctor->save();

            // Asignar especialidades
            if (!empty($doctorData['specialties'])) {
                foreach ($doctorData['specialties'] as $specialtyName) {
                    $specialty = $specialties->where('name', $specialtyName)->first();
                    if ($specialty) {
                        $doctor->specialties()->attach($specialty->id);
                    }
                }
            }

            // Crear horario para el doctor
            $this->createDoctorSchedule($doctor, $company->id);
            
            // Attach doctor to ALL clinics (since we create schedules for all)
            // Ensure we include the role_id in the pivot so each clinic assignment has a role
            $allClinics = \App\Models\ClinicLocation::where('company_id', $company->id)->pluck('id');
            $clinicSync = [];
            foreach ($allClinics as $clinicId) {
                $clinicSync[$clinicId] = ['role_id' => $doctorRole->id];
            }
            $user->clinics()->sync($clinicSync);
            
            // Crear días feriados para el doctor
            $this->createDoctorHolidays($doctor, $company->id);
            
            // Crear descansos para el doctor
            $this->createDoctorBreaks($doctor, $company->id);

            $this->command->info("Doctor creado: {$doctorData['name']}");
        }

        $this->command->info('¡Doctores creados correctamente!');
    }

    /**
     * Crear horario semanal para un doctor (para cada clínica)
     */
    private function createDoctorSchedule(Doctor $doctor, int $companyId): void
    {
        // Fetch all clinics for the company
        $clinics = \App\Models\ClinicLocation::where('company_id', $companyId)->get();

        if ($clinics->isEmpty()) {
            $this->command->warn("No clinics found for company ID {$companyId}. Schedules might not be visible in multi-clinic context.");
        }

        foreach ($clinics as $clinic) {
            // Crear schedule principal para esta clínica
            $schedule = new DoctorSchedule();
            $schedule->doctor_id = $doctor->id;
            $schedule->company_id = $companyId;
            $schedule->clinic_id = $clinic->id;
            $schedule->per_patient_time = '00:30:00'; // 30 minutos por paciente
            $schedule->save();

            // Crear días de la semana (Lunes = 1, Domingo = 7)
            $weekDays = [
                1 => ['from' => '08:00:00', 'to' => '17:00:00'], // Lunes
                2 => ['from' => '08:00:00', 'to' => '17:00:00'], // Martes  
                3 => ['from' => '08:00:00', 'to' => '17:00:00'], // Miércoles
                4 => ['from' => '08:00:00', 'to' => '17:00:00'], // Jueves
                5 => ['from' => '08:00:00', 'to' => '16:00:00'], // Viernes
                6 => ['from' => '09:00:00', 'to' => '13:00:00'], // Sábado (medio día)
                // Domingo = descanso
            ];

            foreach ($weekDays as $dayOfWeek => $hours) {
                $scheduleDay = new DoctorScheduleDay();
                $scheduleDay->doctor_id = $doctor->id;
                $scheduleDay->schedule_id = $schedule->id;
                $scheduleDay->day_of_week = $dayOfWeek;
                $scheduleDay->available_from = $hours['from'];
                $scheduleDay->available_to = $hours['to'];
                $scheduleDay->status = 1;
                $scheduleDay->company_id = $companyId;
                $scheduleDay->save();
            }
        }
    }

    /**
     * Crear días feriados para un doctor
     */
    private function createDoctorHolidays(Doctor $doctor, int $companyId): void
    {
        $currentYear = date('Y');
        
        $holidays = [
            [
                'start_date' => "$currentYear-01-01",
                'end_date' => "$currentYear-01-01",
                'reason' => 'Año Nuevo',
                'holiday_type' => 'public_holiday'
            ],
            [
                'start_date' => "$currentYear-02-05",
                'end_date' => "$currentYear-02-05",
                'reason' => 'Día de la Constitución',
                'holiday_type' => 'public_holiday'
            ],
            [
                'start_date' => "$currentYear-03-21",
                'end_date' => "$currentYear-03-21",
                'reason' => 'Natalicio de Benito Juárez',
                'holiday_type' => 'public_holiday'
            ],
            [
                'start_date' => "$currentYear-05-01",
                'end_date' => "$currentYear-05-01",
                'reason' => 'Día del Trabajo',
                'holiday_type' => 'public_holiday'
            ],
            [
                'start_date' => "$currentYear-09-16",
                'end_date' => "$currentYear-09-16",
                'reason' => 'Día de la Independencia',
                'holiday_type' => 'public_holiday'
            ],
            [
                'start_date' => "$currentYear-11-20",
                'end_date' => "$currentYear-11-20",
                'reason' => 'Día de la Revolución Mexicana',
                'holiday_type' => 'public_holiday'
            ],
            [
                'start_date' => "$currentYear-12-25",
                'end_date' => "$currentYear-12-25",
                'reason' => 'Navidad',
                'holiday_type' => 'public_holiday'
            ],
            [
                'start_date' => "$currentYear-12-31",
                'end_date' => "$currentYear-12-31",
                'reason' => 'Fin de Año',
                'holiday_type' => 'public_holiday'
            ]
        ];

        // Agregar algunos días feriados aleatorios para algunos doctores
        $randomHolidays = [
            [
                'start_date' => "$currentYear-07-15",
                'end_date' => "$currentYear-07-21",
                'reason' => 'Vacaciones de verano',
                'holiday_type' => 'vacation'
            ],
            [
                'start_date' => "$currentYear-08-20",
                'end_date' => "$currentYear-08-20",
                'reason' => 'Día personal',
                'holiday_type' => 'personal'
            ],
            [
                'start_date' => "$currentYear-10-10",
                'end_date' => "$currentYear-10-12",
                'reason' => 'Conferencia médica',
                'holiday_type' => 'conference'
            ]
        ];

        // Todos los doctores tienen los días feriados nacionales
        foreach ($holidays as $holiday) {
            $doctorHoliday = new DoctorHoliday();
            $doctorHoliday->doctor_id = $doctor->id;
            $doctorHoliday->start_date = $holiday['start_date'];
            $doctorHoliday->end_date = $holiday['end_date'];
            $doctorHoliday->reason = $holiday['reason'];
            $doctorHoliday->holiday_type = $holiday['holiday_type'];
            $doctorHoliday->status = 'approved';
            $doctorHoliday->company_id = $companyId;
            $doctorHoliday->save();
        }

        // Algunos doctores tienen días adicionales (50% de probabilidad)
        if (rand(0, 1)) {
            $selectedRandomHolidays = array_rand($randomHolidays, rand(1, 2));
            if (!is_array($selectedRandomHolidays)) {
                $selectedRandomHolidays = [$selectedRandomHolidays];
            }
            
            foreach ($selectedRandomHolidays as $index) {
                $holiday = $randomHolidays[$index];
                $doctorHoliday = new DoctorHoliday();
                $doctorHoliday->doctor_id = $doctor->id;
                $doctorHoliday->start_date = $holiday['start_date'];
                $doctorHoliday->end_date = $holiday['end_date'];
                $doctorHoliday->reason = $holiday['reason'];
                $doctorHoliday->holiday_type = $holiday['holiday_type'];
                $doctorHoliday->status = 'approved';
                $doctorHoliday->company_id = $companyId;
                $doctorHoliday->save();
            }
        }
    }

    /**
     * Crear descansos para un doctor
     */
    private function createDoctorBreaks(Doctor $doctor, int $companyId): void
    {
        // Descanso para almuerzo (todos los días)
        $lunchBreak = new DoctorBreak();
        $lunchBreak->doctor_id = $doctor->id;
        $lunchBreak->break_from = '13:00:00';
        $lunchBreak->break_to = '14:00:00';
        $lunchBreak->every_day = 1; // Todos los días
        $lunchBreak->date = null; // No es para una fecha específica
        $lunchBreak->company_id = $companyId;
        $lunchBreak->save();

        // Descanso corto por la mañana (algunos doctores)
        if (rand(0, 1)) {
            $morningBreak = new DoctorBreak();
            $morningBreak->doctor_id = $doctor->id;
            $morningBreak->break_from = '10:30:00';
            $morningBreak->break_to = '10:45:00';
            $morningBreak->every_day = 1;
            $morningBreak->date = null;
            $morningBreak->company_id = $companyId;
            $morningBreak->save();
        }

        // Descanso por la tarde (algunos doctores)
        if (rand(0, 1)) {
            $afternoonBreak = new DoctorBreak();
            $afternoonBreak->doctor_id = $doctor->id;
            $afternoonBreak->break_from = '15:30:00';
            $afternoonBreak->break_to = '15:45:00';
            $afternoonBreak->every_day = 1;
            $afternoonBreak->date = null;
            $afternoonBreak->company_id = $companyId;
            $afternoonBreak->save();
        }

        // Descanso específico para una fecha (reunión, cita médica, etc.)
        if (rand(0, 2) == 0) { // 33% de probabilidad
            $currentDate = date('Y-m-d');
            $futureDate = date('Y-m-d', strtotime($currentDate . ' +' . rand(1, 30) . ' days'));
            
            $specificBreak = new DoctorBreak();
            $specificBreak->doctor_id = $doctor->id;
            $specificBreak->break_from = '11:00:00';
            $specificBreak->break_to = '12:00:00';
            $specificBreak->every_day = 0;
            $specificBreak->date = $futureDate;
            $specificBreak->company_id = $companyId;
            $specificBreak->save();
        }
    }
}