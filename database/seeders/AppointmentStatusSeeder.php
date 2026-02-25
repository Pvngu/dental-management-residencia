<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Room;
use App\Models\TreatmentType;
use Carbon\Carbon;

class AppointmentStatusSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener registros existentes para las relaciones
        $patients = Patient::limit(3)->get();
        $doctors = Doctor::limit(2)->get();
        $rooms = Room::limit(2)->get();
        $treatmentTypes = TreatmentType::limit(3)->get();
        $companyId = 1; // Asume que existe una compañía con ID 1

        if ($patients->isEmpty() || $doctors->isEmpty()) {
            $this->command->info('No hay pacientes o doctores disponibles. Por favor ejecuta otros seeders primero.');
            return;
        }

        $today = Carbon::today();
        // Simplified status values (booking state)
        $bookingStatuses = ['pending', 'confirmed', 'cancelled', 'completed'];
        // Flow states are now derived from datetime fields, not stored
        $flowStates = ['scheduled', 'checked_in', 'in_progress', 'completed', 'checked_out'];
        $reasons = [
            'Routine Dental Cleaning',
            'Dental Check-up',
            'Tooth Filling',
            'Root Canal Treatment',
            'Dental Consultation',
            'Teeth Whitening',
            'Oral Surgery'
        ];

        // Crear appointments de ejemplo
        for ($i = 0; $i < 10; $i++) {
            $appointmentDate = $today->copy()->addHours(rand(8, 18))->addMinutes(rand(0, 59));
            $flowState = $flowStates[array_rand($flowStates)];
            // Set booking status based on flow state
            $bookingStatus = in_array($flowState, ['completed', 'checked_out']) ? 'completed' : 'confirmed';
            
            $appointment = [
                'appointment_date' => $appointmentDate,
                'duration' => rand(30, 120), // 30 a 120 minutos
                'treatment_details' => 'Treatment details for appointment ' . ($i + 1),
                'status' => $bookingStatus, // Booking status (pending/confirmed/cancelled/completed)
                'reason_visit' => $reasons[array_rand($reasons)],
                'appointment_notes' => $this->generateNotes($flowState),
                'patient_id' => $patients->random()->id,
                'doctor_id' => $doctors->random()->id,
                'company_id' => $companyId,
            ];

            // Agregar room_id si hay rooms disponibles
            if (!$rooms->isEmpty()) {
                $appointment['room_id'] = $rooms->random()->id;
            }

            // Agregar treatment_type_id si hay tipos de tratamiento disponibles
            if (!$treatmentTypes->isEmpty()) {
                $appointment['treatment_type_id'] = $treatmentTypes->random()->id;
            }

            // Set datetime fields based on the flow state
            // Flow: scheduled -> checked_in -> in_progress -> completed -> checked_out
            switch ($flowState) {
                case 'checked_in':
                    $appointment['arrive_datetime'] = $appointmentDate->copy()->subMinutes(rand(5, 15));
                    $appointment['checkin_datetime'] = $appointmentDate->copy()->subMinutes(rand(0, 10));
                    break;
                    
                case 'in_progress':
                    $appointment['arrive_datetime'] = $appointmentDate->copy()->subMinutes(rand(15, 30));
                    $appointment['checkin_datetime'] = $appointmentDate->copy()->subMinutes(rand(10, 20));
                    $appointment['in_progress_datetime'] = $appointmentDate->copy()->subMinutes(rand(0, 5));
                    break;
                    
                case 'completed':
                    $appointment['arrive_datetime'] = $appointmentDate->copy()->subMinutes(rand(60, 90));
                    $appointment['checkin_datetime'] = $appointmentDate->copy()->subMinutes(rand(50, 70));
                    $appointment['in_progress_datetime'] = $appointmentDate->copy()->subMinutes(rand(40, 55));
                    $appointment['completed_datetime'] = $appointmentDate->copy()->addMinutes(rand(30, 60));
                    break;
                    
                case 'checked_out':
                    $appointment['arrive_datetime'] = $appointmentDate->copy()->subMinutes(rand(90, 120));
                    $appointment['checkin_datetime'] = $appointmentDate->copy()->subMinutes(rand(80, 100));
                    $appointment['in_progress_datetime'] = $appointmentDate->copy()->subMinutes(rand(60, 75));
                    $appointment['completed_datetime'] = $appointmentDate->copy()->addMinutes(rand(30, 60));
                    $appointment['checkout_datetime'] = $appointmentDate->copy()->addMinutes(rand(70, 100));
                    break;
                    
                // 'scheduled' - no datetime fields set
            }

            Appointment::create($appointment);
        }

        $this->command->info('Se crearon 10 appointments de ejemplo con diferentes status.');
    }

    private function generateNotes($flowState)
    {
        $notes = [
            'scheduled' => [
                'Appointment scheduled',
                'Patient confirmed appointment',
                'Awaiting patient arrival'
            ],
            'checked_in' => [
                'Patient checked in successfully',
                'Patient ready for treatment',
                'Patient completed paperwork and is ready'
            ],
            'in_progress' => [
                'Treatment in progress',
                'Doctor is with the patient',
                'Procedure started'
            ],
            'completed' => [
                'Treatment completed successfully',
                'Patient satisfied with treatment',
                'Follow-up scheduled for next month'
            ],
            'checked_out' => [
                'Patient checked out and paid',
                'Treatment completed, patient discharged',
                'Next appointment scheduled during checkout'
            ]
        ];

        $stateNotes = $notes[$flowState] ?? ['Standard appointment notes'];
        return $stateNotes[array_rand($stateNotes)];
    }
}
