<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentHistory;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Enums\AppointmentAction;

class AppointmentHistoryService
{
    /**
     * Log a 'booked' event for an appointment.
     *
     * @param Appointment $appointment
     * @return void
     */
    public function logBooked(Appointment $appointment)
    {
        try {
            $userId = auth()->id();

            $duration = $appointment->duration;
            $date = Carbon::parse($appointment->appointment_date)->format('d M Y');
            $time = Carbon::parse($appointment->appointment_date)->format('h:i A');
            $patientName = $appointment->patient ? $appointment->patient->name : 'Patient';
            $doctorName = $appointment->doctor ? $appointment->doctor->name : 'Doctor';

            $this->createHistory($appointment->id, AppointmentAction::BOOKED, "Booked {$duration} Minutes Meeting on {$date} at {$time} for {$patientName} with {$doctorName}");
        } catch (\Exception $e) {
            Log::error('Failed to log appointment booked history: ' . $e->getMessage());
        }
    }

    /**
     * Log a 'rescheduled' event if the date/time has changed.
     *
     * @param Appointment $appointment
     * @param string|null $oldDate
     * @return void
     */
    public function logRescheduled(Appointment $appointment, $oldDate)
    {
        if (!$oldDate || $oldDate == $appointment->appointment_date) {
            return;
        }

        try {
            $oldD = Carbon::parse($oldDate)->format('d M Y');
            $oldT = Carbon::parse($oldDate)->format('h:i A');
            $newD = Carbon::parse($appointment->appointment_date)->format('d M Y');
            $newT = Carbon::parse($appointment->appointment_date)->format('h:i A');

            $this->createHistory(
                $appointment->id,
                AppointmentAction::RESCHEDULED,
                "Rescheduled {$appointment->duration} Minutes Meeting from {$oldD} at {$oldT} to {$newD} at {$newT}"
            );
        } catch (\Exception $e) {
            Log::error('Failed to log appointment rescheduled history: ' . $e->getMessage());
        }
    }

    /**
     * Log a 'doctor_change' event if the doctor has changed.
     *
     * @param Appointment $appointment
     * @param int|string|null $oldDoctorId
     * @return void
     */
    public function logDoctorChange(Appointment $appointment, $oldDoctorId)
    {
        if (!$oldDoctorId || $oldDoctorId == $appointment->doctor_id) {
            return;
        }

        try {
            $oldDoctor = Doctor::find($oldDoctorId);
            $newDoctor = Doctor::find($appointment->doctor_id);
            $oldName = $oldDoctor ? $oldDoctor->user->name : 'Unknown';
            $newName = $newDoctor ? $newDoctor->user->name : 'Unknown';

            $this->createHistory(
                $appointment->id,
                AppointmentAction::DOCTOR_CHANGED,
                "Doctor changed from {$oldName} to {$newName}"
            );
        } catch (\Exception $e) {
            Log::error('Failed to log appointment doctor change history: ' . $e->getMessage());
        }
    }

    /**
     * Helper to create the history record.
     *
     * @param int $appointmentId
     * @param string $actionType
     * @param string $description
     * @return void
     */
    protected function createHistory($appointmentId, $actionType, $description)
    {   
        AppointmentHistory::create([
            'appointment_id' => $appointmentId,
            'user_id' => user()->id,
            'action_type' => $actionType,
            'description' => $description,
        ]);
    }
}
