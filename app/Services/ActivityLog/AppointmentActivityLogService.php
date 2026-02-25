<?php

namespace App\Services\ActivityLog;

use App\Models\Appointment;

class AppointmentActivityLogService extends BaseActivityLogService
{
    /**
     * Log appointment creation
     */
    public function logAppointmentCreated(Appointment $appointment)
    {
        $patientName = $this->getPatientName($appointment->patient_id);
        $doctorName = $this->getDoctorName($appointment->doctor_id);
        $appointmentDate = $appointment->appointment_date ? $appointment->appointment_date->format('Y-m-d H:i') : 'N/A';
        
        return $this->logActivity(
            'CREATED',
            'appointments',
            ActivityLogMessageProvider::getLocalizedMessage('appointment_created', [
                'patient_name' => $patientName,
                'appointment_date' => $appointmentDate,
                'doctor_name' => $doctorName
            ]),
            $appointment->company_id
        );
    }

    /**
     * Log appointment update
     */
    public function logAppointmentUpdated(Appointment $appointment)
    {
        $changes = $this->formatChanges($appointment);
        
        if (!empty($changes)) {
            $patientName = $this->getPatientName($appointment->patient_id);
            
            return $this->logActivity(
                'UPDATED',
                'appointments',
                ActivityLogMessageProvider::getLocalizedMessage('appointment_updated', [
                    'patient_name' => $patientName,
                    'changes' => $changes
                ]),
                $appointment->company_id
            );
        }
        
        return null;
    }

    /**
     * Log appointment deletion
     */
    public function logAppointmentDeleted(Appointment $appointment)
    {
        $patientName = $this->getPatientName($appointment->patient_id);
        $appointmentDate = $appointment->appointment_date ? $appointment->appointment_date->format('Y-m-d H:i') : 'N/A';
        
        return $this->logActivity(
            'DELETED',
            'appointments',
            ActivityLogMessageProvider::getLocalizedMessage('appointment_deleted', [
                'patient_name' => $patientName,
                'appointment_date' => $appointmentDate
            ]),
            $appointment->company_id
        );
    }
}