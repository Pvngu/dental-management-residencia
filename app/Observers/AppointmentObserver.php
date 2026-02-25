<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\PatientHistory;
use App\Services\ActivityLog\AppointmentActivityLogService;
use App\Services\AppointmentHistoryService;

class AppointmentObserver
{
    protected $activityLogService;
    protected $historyService;

    public function __construct()
    {
        $this->activityLogService = new AppointmentActivityLogService();
        $this->historyService = new AppointmentHistoryService();
    }

    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment): void
    {
        $data = [
            'appointment_id' => $appointment->id,
            'appointment_date' => $appointment->appointment_date,
            'status' => $appointment->status,
            'doctor_id' => $appointment->doctor_id,
            'treatment_type_id' => $appointment->treatment_type_id,
            'room_id' => $appointment->room_id,
            'duration' => $appointment->duration,
            'treatment_details' => $appointment->treatment_details,
        ];

        PatientHistory::createEntry(
            $appointment->patient_id,
            'appointment_created',
            $data,
            null,
            $appointment
        );

        $this->historyService->logBooked($appointment);
        $this->activityLogService->logAppointmentCreated($appointment);
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment): void
    {
        $original = $appointment->getOriginal();
        $changes = [];

        foreach ($appointment->getDirty() as $key => $value) {
            if (in_array($key, ['updated_at'])) {
                continue;
            }

            $changes[$key] = [
                'old' => array_key_exists($key, $original) ? $original[$key] : null,
                'new' => $value
            ];
        }

        if (!empty($changes)) {
            $data = [
                'appointment_id' => $appointment->id,
                'changes' => $changes,
                'current_status' => $appointment->status,
                'current_date' => $appointment->appointment_date,
            ];

            PatientHistory::createEntry(
                $appointment->patient_id,
                'appointment_updated',
                $data,
                null,
                $appointment
            );

            $this->activityLogService->logAppointmentUpdated($appointment);
        }

        if (array_key_exists('appointment_date', $original) && $original['appointment_date'] != $appointment->appointment_date) {
            $this->historyService->logRescheduled($appointment, $original['appointment_date']);
        }
        
        if (array_key_exists('doctor_id', $original) && $original['doctor_id'] != $appointment->doctor_id) {
            $this->historyService->logDoctorChange($appointment, $original['doctor_id']);
        }
    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment): void
    {
        $data = [
            'appointment_id' => $appointment->id,
            'appointment_date' => $appointment->appointment_date,
            'status' => $appointment->status,
            'doctor_id' => $appointment->doctor_id,
            'deleted_at' => now(),
        ];

        PatientHistory::createEntry(
            $appointment->patient_id,
            'appointment_deleted',
            $data,
            null,
            $appointment
        );

        $this->activityLogService->logAppointmentDeleted($appointment);
    }
}
