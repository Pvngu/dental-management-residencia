<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Room;
use App\Models\TreatmentType;
use App\Models\Company;
use App\Models\ClinicLocation;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class AppointmentSeeder extends Seeder
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

        $patients = Patient::all();
        $doctors = Doctor::all();
        $rooms = Room::where('status', 'Available')->get();
        $treatmentTypes = TreatmentType::where('is_active', true)->get();

        if ($patients->isEmpty() || $doctors->isEmpty() || $rooms->isEmpty() || $treatmentTypes->isEmpty()) {
            $this->command->error('No se encontraron pacientes, doctores, salas o tipos de tratamiento. Por favor ejecute esos seeders primero.');
            return;
        }

        // Crear citas para los próximos 30 días
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);

        $appointments = [];

        // Citas pasadas (últimos 15 días) - Completadas
        for ($i = 15; $i >= 1; $i--) {
            $date = Carbon::today()->subDays($i);
            
            // Solo días laborables (lunes a sábado)
            if ($date->dayOfWeek >= 1 && $date->dayOfWeek <= 6) {
                $appointments = array_merge($appointments, $this->generateAppointmentsForDate($date, $patients, $doctors, $rooms, $treatmentTypes, 'past'));
            }
        }

        // Citas de hoy
        $today = Carbon::today();
        if ($today->dayOfWeek >= 1 && $today->dayOfWeek <= 6) {
            $appointments = array_merge($appointments, $this->generateAppointmentsForDate($today, $patients, $doctors, $rooms, $treatmentTypes, 'today'));
        }

        // Citas futuras (próximos 30 días)
        for ($i = 1; $i <= 30; $i++) {
            $date = Carbon::today()->addDays($i);
            
            // Solo días laborables (lunes a sábado)
            if ($date->dayOfWeek >= 1 && $date->dayOfWeek <= 6) {
                $appointments = array_merge($appointments, $this->generateAppointmentsForDate($date, $patients, $doctors, $rooms, $treatmentTypes, 'future'));
            }
        }

        foreach ($appointments as $appointmentData) {
            Appointment::create($appointmentData);
        }

        $this->command->info('¡Citas médicas creadas correctamente!');
        $this->command->info('Total de citas creadas: ' . count($appointments));
    }

    private function generateAppointmentsForDate($date, $patients, $doctors, $rooms, $treatmentTypes, $timeFrame): array
    {
        $appointments = [];
        $company = Company::first();
        $clinics = ClinicLocation::where('company_id', $company->id)->get();
        
        // Horarios de trabajo según el día
        if ($date->dayOfWeek == 6) { // Sábado
            $startHour = 9;
            $endHour = 13;
        } else { // Lunes a Viernes
            $startHour = 8;
            $endHour = ($date->dayOfWeek == 5) ? 16 : 17; // Viernes hasta 16:00
        }

        // Generar entre 3-8 citas por día según el marco temporal
        switch ($timeFrame) {
            case 'past':
                $numAppointments = rand(4, 8);
                break;
            case 'today':
                $numAppointments = rand(6, 10);
                break;
            case 'future':
                $numAppointments = rand(3, 7);
                break;
            default:
                $numAppointments = 5;
        }

        $usedTimes = [];

        for ($i = 0; $i < $numAppointments; $i++) {
            // Seleccionar elementos aleatorios
            $patient = $patients->random();
            $doctor = $doctors->random();
            $room = $rooms->random();
            $treatmentType = $treatmentTypes->random();

            // Generar hora aleatoria evitando conflictos
            do {
                $hour = rand($startHour, $endHour - 1);
                $minute = rand(0, 1) * 30; // 00 o 30 minutos
                $timeKey = sprintf('%02d:%02d', $hour, $minute);
            } while (in_array($timeKey, $usedTimes));

            $usedTimes[] = $timeKey;

            $appointmentDateTime = $date->copy()->setTime($hour, $minute);

            // Determinar estado y datetime fields según el marco temporal
            $appointmentData = $this->getAppointmentDataByTimeFrame($timeFrame, $appointmentDateTime, $treatmentType);

            // Generar detalles del tratamiento
            $treatmentDetails = $this->generateTreatmentDetails($treatmentType);

            // Assign random clinic
            $clinic = $clinics->isNotEmpty() ? $clinics->random() : null;

            $appointments[] = array_merge([
                'company_id' => $company->id,
                'clinic_id' => $clinic ? $clinic->id : null,
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'room_id' => $room->id,
                'treatment_type_id' => $treatmentType->id,
                'appointment_date' => $appointmentDateTime,
                'duration' => $treatmentType->duration_minutes,
                'treatment_details' => $treatmentDetails,
                'created_at' => now(),
                'updated_at' => now(),
            ], $appointmentData);
        }

        return $appointments;
    }

    /**
     * Get appointment data (status + datetime fields) based on time frame.
     * Valid status values: pending, confirmed, cancelled, completed
     * Flow status is derived from datetime fields: scheduled, checked_in, in_progress, completed, checked_out
     */
    private function getAppointmentDataByTimeFrame($timeFrame, $appointmentDateTime, $treatmentType): array
    {
        $data = ['status' => 'confirmed'];
        $duration = $treatmentType->duration_minutes;

        switch ($timeFrame) {
            case 'past':
                // Citas pasadas: 80% completadas (checked_out), 15% canceladas, 5% completadas sin checkout
                $rand = rand(1, 100);
                if ($rand <= 80) {
                    // Completed and checked out
                    $data['status'] = 'completed';
                    $data['arrive_datetime'] = $appointmentDateTime->copy()->subMinutes(rand(10, 20));
                    $data['checkin_datetime'] = $appointmentDateTime->copy()->subMinutes(rand(5, 10));
                    $data['in_progress_datetime'] = $appointmentDateTime->copy();
                    $data['completed_datetime'] = $appointmentDateTime->copy()->addMinutes($duration);
                    $data['checkout_datetime'] = $appointmentDateTime->copy()->addMinutes($duration + rand(5, 15));
                } elseif ($rand <= 95) {
                    // Cancelled
                    $data['status'] = 'cancelled';
                } else {
                    // Completed but not checked out (forgot checkout)
                    $data['status'] = 'completed';
                    $data['arrive_datetime'] = $appointmentDateTime->copy()->subMinutes(rand(10, 20));
                    $data['checkin_datetime'] = $appointmentDateTime->copy()->subMinutes(rand(5, 10));
                    $data['in_progress_datetime'] = $appointmentDateTime->copy();
                    $data['completed_datetime'] = $appointmentDateTime->copy()->addMinutes($duration);
                }
                break;

            case 'today':
                // Citas de hoy: variedad de estados basados en la hora
                $now = Carbon::now();
                if ($appointmentDateTime->isPast()) {
                    $minutesSinceAppointment = $now->diffInMinutes($appointmentDateTime);
                    $rand = rand(1, 100);
                    
                    if ($minutesSinceAppointment > $duration + 30) {
                        // Should be completed by now
                        if ($rand <= 70) {
                            // Completed and checked out
                            $data['status'] = 'completed';
                            $data['arrive_datetime'] = $appointmentDateTime->copy()->subMinutes(rand(10, 20));
                            $data['checkin_datetime'] = $appointmentDateTime->copy()->subMinutes(rand(5, 10));
                            $data['in_progress_datetime'] = $appointmentDateTime->copy();
                            $data['completed_datetime'] = $appointmentDateTime->copy()->addMinutes($duration);
                            $data['checkout_datetime'] = $appointmentDateTime->copy()->addMinutes($duration + rand(5, 15));
                        } else {
                            // Completed but not checked out yet
                            $data['status'] = 'completed';
                            $data['arrive_datetime'] = $appointmentDateTime->copy()->subMinutes(rand(10, 20));
                            $data['checkin_datetime'] = $appointmentDateTime->copy()->subMinutes(rand(5, 10));
                            $data['in_progress_datetime'] = $appointmentDateTime->copy();
                            $data['completed_datetime'] = $appointmentDateTime->copy()->addMinutes($duration);
                        }
                    } elseif ($minutesSinceAppointment > 10) {
                        // In progress
                        $data['status'] = 'confirmed';
                        $data['arrive_datetime'] = $appointmentDateTime->copy()->subMinutes(rand(10, 20));
                        $data['checkin_datetime'] = $appointmentDateTime->copy()->subMinutes(rand(5, 10));
                        $data['in_progress_datetime'] = $appointmentDateTime->copy();
                    } else {
                        // Just checked in
                        $data['status'] = 'confirmed';
                        $data['arrive_datetime'] = $appointmentDateTime->copy()->subMinutes(rand(5, 15));
                        $data['checkin_datetime'] = $appointmentDateTime->copy()->subMinutes(rand(0, 5));
                    }
                } else {
                    // Future appointment today
                    $rand = rand(1, 100);
                    if ($rand <= 80) {
                        $data['status'] = 'confirmed';
                    } else {
                        $data['status'] = 'pending';
                    }
                }
                break;

            case 'future':
                // Citas futuras: 70% confirmadas, 25% pendientes, 5% canceladas
                $rand = rand(1, 100);
                if ($rand <= 70) {
                    $data['status'] = 'confirmed';
                } elseif ($rand <= 95) {
                    $data['status'] = 'pending';
                } else {
                    $data['status'] = 'cancelled';
                }
                break;

            default:
                $data['status'] = 'pending';
        }

        return $data;
    }

    private function generateTreatmentDetails($treatmentType): string
    {
        $details = [
            'Consulta General' => [
                'Examen clínico completo',
                'Revisión de higiene oral',
                'Evaluación de caries y encías',
                'Recomendaciones preventivas'
            ],
            'Limpieza Dental (Profilaxis)' => [
                'Remoción de placa y sarro',
                'Pulido dental',
                'Aplicación de flúor',
                'Instrucciones de higiene oral'
            ],
            'Empaste de Resina' => [
                'Remoción de caries en molar superior',
                'Restauración con resina compuesta color A2',
                'Ajuste de oclusión',
                'Recomendaciones post-tratamiento'
            ],
            'Extracción Simple' => [
                'Aplicación de anestesia local',
                'Extracción de pieza dental 38',
                'Sutura si es necesario',
                'Indicaciones post-extracción'
            ],
            'Endodoncia (Tratamiento de Conducto)' => [
                'Apertura cameral',
                'Instrumentación de conductos',
                'Obturación temporal',
                'Cita de control programada'
            ],
            'Corona Dental' => [
                'Preparación del diente',
                'Toma de impresión',
                'Colocación de corona temporal',
                'Programación para entrega de corona definitiva'
            ]
        ];

        $treatmentName = $treatmentType->name;
        
        if (isset($details[$treatmentName])) {
            return implode('. ', $details[$treatmentName]) . '.';
        }

        // Detalles genéricos si no se encuentra el tratamiento específico
        return "Tratamiento de {$treatmentType->name}. Evaluación inicial. Procedimiento según protocolo estándar. Seguimiento programado.";
    }
}