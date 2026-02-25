<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;

class NotificationService
{
    /**
     * Create notification for appointment created
     * Target: doctor
     */
    public static function appointmentCreated($appointment)
    {
        $appointmentDate = \Carbon\Carbon::parse($appointment->appointment_date);
        $patientName = ($appointment->patient && $appointment->patient->user) 
            ? $appointment->patient->user->name . ' ' . $appointment->patient->user->last_name
            : 'Paciente';
        $doctorName = ($appointment->doctor && $appointment->doctor->user) 
            ? $appointment->doctor->user->name . ' ' . $appointment->doctor->user->last_name
            : 'Doctor';
        
        $treatmentTitle = $appointment->treatment_details ?? 'Cita';
        $message = "Nueva cita programada: '{$treatmentTitle} para {$patientName}' el {$appointmentDate->format('d/m/Y')} a las {$appointmentDate->format('H:i')}";

        $data = [
            'message' => $message,
            'url' => url("/admin/appointments?view={$appointment->xid}"),
            'appointment_id' => $appointment->id,
            'appointment_xid' => $appointment->xid,
            'titulo' => "{$treatmentTitle} para {$patientName}",
            'fecha' => $appointmentDate->format('Y-m-d'),
            'hora_inicio' => $appointmentDate->format('H:i'),
            'hora_fin' => $appointmentDate->copy()->addMinutes($appointment->duration ?? 60)->format('H:i'),
            'tipo' => $appointment->treatmentType->name ?? 'Consulta General',
            'paciente_id' => $appointment->patient_id ?? null,
            'paciente_nombre' => $patientName,
            'doctor_nombre' => $doctorName,
            'ubicacion' => $appointment->room->name ?? 'Sin asignar',
        ];

        // Notify the assigned doctor
        if ($appointment->doctor_id) {
            self::createNotification($appointment->doctor_id, $message, 'appointment_created', $data, false);
        }
    }

    /**
     * Create notification for appointment cancelled
     * Target: doctor
     */
    public static function appointmentCancelled($appointment)
    {
        $appointmentDate = \Carbon\Carbon::parse($appointment->appointment_date);
        $patientName = ($appointment->patient && $appointment->patient->user) 
            ? $appointment->patient->user->name . ' ' . $appointment->patient->user->last_name
            : 'Paciente';
        $doctorName = ($appointment->doctor && $appointment->doctor->user) 
            ? $appointment->doctor->user->name . ' ' . $appointment->doctor->user->last_name
            : 'Doctor';
        
        $treatmentTitle = $appointment->treatment_details ?? 'Cita';
        $message = "Cita cancelada: '{$treatmentTitle} para {$patientName}' originalmente el {$appointmentDate->format('d/m/Y')} a las {$appointmentDate->format('H:i')}";

        $data = [
            'message' => $message,
            'url' => url("/admin/appointments?view={$appointment->xid}"),
            'appointment_id' => $appointment->id,
            'appointment_xid' => $appointment->xid,
            'titulo' => "{$treatmentTitle} para {$patientName}",
            'fecha' => $appointmentDate->format('Y-m-d'),
            'hora_inicio' => $appointmentDate->format('H:i'),
            'hora_fin' => $appointmentDate->copy()->addMinutes($appointment->duration ?? 60)->format('H:i'),
            'tipo' => $appointment->treatmentType->name ?? 'Urgencia',
            'paciente_id' => $appointment->patient_id ?? null,
            'paciente_nombre' => $patientName,
            'doctor_nombre' => $doctorName,
            'ubicacion' => $appointment->room->name ?? 'Sin asignar',
        ];

        // Notify the assigned doctor (important)
        if ($appointment->doctor_id) {
            self::createNotification($appointment->doctor_id, $message, 'appointment_cancelled', $data, true);
        }
    }

    /**
     * Create notification for follow-up reminder
     * Target: doctor
     */
    public static function followupReminder($appointment)
    {
        $appointmentDate = \Carbon\Carbon::parse($appointment->appointment_date);
        $patientName = ($appointment->patient && $appointment->patient->user) 
            ? $appointment->patient->user->name . ' ' . $appointment->patient->user->last_name
            : 'Paciente';
        $doctorName = ($appointment->doctor && $appointment->doctor->user) 
            ? $appointment->doctor->user->name . ' ' . $appointment->doctor->user->last_name
            : 'Doctor';
        
        $treatmentTitle = $appointment->treatment_details ?? 'Revisión post-tratamiento';
        $message = "Recordatorio: '{$treatmentTitle} para {$patientName}' el {$appointmentDate->format('d/m/Y')} a las {$appointmentDate->format('H:i')}";

        $data = [
            'message' => $message,
            'url' => url("/admin/appointments?view={$appointment->xid}"),
            'appointment_id' => $appointment->id,
            'appointment_xid' => $appointment->xid,
            'titulo' => "{$treatmentTitle} para {$patientName}",
            'fecha' => $appointmentDate->format('Y-m-d'),
            'hora_inicio' => $appointmentDate->format('H:i'),
            'hora_fin' => $appointmentDate->copy()->addMinutes($appointment->duration ?? 60)->format('H:i'),
            'tipo' => 'Seguimiento',
            'paciente_id' => $appointment->patient_id ?? null,
            'paciente_nombre' => $patientName,
            'doctor_nombre' => $doctorName,
            'ubicacion' => $appointment->room->name ?? 'Sin asignar',
        ];

        // Notify the assigned doctor
        if ($appointment->doctor_id) {
            self::createNotification($appointment->doctor_id, $message, 'followup_reminder', $data, false);
        }
    }

    /**
     * Create notification for patient checked in
     * Target: doctor
     */
    public static function patientCheckedIn($appointment)
    {
        $appointmentDate = \Carbon\Carbon::parse($appointment->appointment_date);
        $patientName = ($appointment->patient && $appointment->patient->user) 
            ? $appointment->patient->user->name . ' ' . $appointment->patient->user->last_name
            : 'Paciente';
        $doctorName = ($appointment->doctor && $appointment->doctor->user) 
            ? $appointment->doctor->user->name . ' ' . $appointment->doctor->user->last_name
            : 'Doctor';
        
        $treatmentTitle = $appointment->treatment_details ?? 'Cita';
        $message = "El paciente '{$patientName}' ha llegado para su cita de '{$treatmentTitle}' programada a las {$appointmentDate->format('H:i')}";

        $data = [
            'message' => $message,
            'url' => url("/admin/appointments?view={$appointment->xid}"),
            'appointment_id' => $appointment->id,
            'appointment_xid' => $appointment->xid,
            'titulo' => "{$treatmentTitle} para {$patientName}",
            'fecha' => $appointmentDate->format('Y-m-d'),
            'hora_inicio' => $appointmentDate->format('H:i'),
            'hora_fin' => $appointmentDate->copy()->addMinutes($appointment->duration ?? 60)->format('H:i'),
            'tipo' => $appointment->treatmentType->name ?? 'Consulta',
            'paciente_id' => $appointment->patient_id ?? null,
            'paciente_nombre' => $patientName,
            'doctor_nombre' => $doctorName,
            'ubicacion' => $appointment->room->name ?? 'Sin asignar',
        ];

        // Notify the assigned doctor (important - patient has arrived)
        if ($appointment->doctor_id) {
            self::createNotification($appointment->doctor_id, $message, 'patient_checked_in', $data, true);
        }
    }

    /**
     * Create notification for appointment completed
     * Target: receptionist
     */
    public static function appointmentCompleted($appointment)
    {
        $appointmentDate = \Carbon\Carbon::parse($appointment->appointment_date);
        $patientName = ($appointment->patient && $appointment->patient->user) 
            ? $appointment->patient->user->name . ' ' . $appointment->patient->user->last_name
            : 'Paciente';
        $doctorName = ($appointment->doctor && $appointment->doctor->user) 
            ? $appointment->doctor->user->name . ' ' . $appointment->doctor->user->last_name
            : 'Doctor';
        
        $treatmentTitle = $appointment->treatment_details ?? 'Cita';
        $message = "Cita completada: '{$treatmentTitle} para {$patientName}' finalizada a las {$appointmentDate->format('H:i')}";

        $data = [
            'message' => $message,
            'url' => url("/admin/appointments?view={$appointment->xid}"),
            'appointment_id' => $appointment->id,
            'appointment_xid' => $appointment->xid,
            'titulo' => "{$treatmentTitle} para {$patientName}",
            'fecha' => $appointmentDate->format('Y-m-d'),
            'hora_inicio' => $appointmentDate->format('H:i'),
            'hora_fin' => $appointmentDate->copy()->addMinutes($appointment->duration ?? 60)->format('H:i'),
            'tipo' => $appointment->treatmentType->name ?? 'Consulta',
            'paciente_id' => $appointment->patient_id ?? null,
            'paciente_nombre' => $patientName,
            'doctor_nombre' => $doctorName,
            'ubicacion' => $appointment->room->name ?? 'Sin asignar',
        ];

        // Notify all receptionists
        self::notifyReceptionists($message, 'appointment_completed', $data, false);
    }

    /**
     * Create notification for appointment rescheduled
     * Target: doctor
     */
    public static function appointmentRescheduled($appointment, $oldDate, $newDate)
    {
        $oldDateTime = \Carbon\Carbon::parse($oldDate);
        $newDateTime = \Carbon\Carbon::parse($newDate);
        $patientName = ($appointment->patient && $appointment->patient->user) 
            ? $appointment->patient->user->name . ' ' . $appointment->patient->user->last_name
            : 'Paciente';
        $doctorName = ($appointment->doctor && $appointment->doctor->user) 
            ? $appointment->doctor->user->name . ' ' . $appointment->doctor->user->last_name
            : 'Doctor';
        
        $treatmentTitle = $appointment->treatment_details ?? 'Cita';
        $message = "La cita de '{$treatmentTitle} para {$patientName}' ha sido reprogramada al {$newDateTime->format('d/m/Y')} a las {$newDateTime->format('H:i')}";

        $data = [
            'message' => $message,
            'url' => url("/admin/appointments?view={$appointment->xid}"),
            'appointment_id' => $appointment->id,
            'appointment_xid' => $appointment->xid,
            'titulo' => "{$treatmentTitle} para {$patientName}",
            'fecha' => $newDateTime->format('Y-m-d'),
            'hora_inicio' => $newDateTime->format('H:i'),
            'hora_fin' => $newDateTime->copy()->addMinutes($appointment->duration ?? 60)->format('H:i'),
            'tipo' => $appointment->treatmentType->name ?? 'Consulta',
            'paciente_id' => $appointment->patient_id ?? null,
            'paciente_nombre' => $patientName,
            'doctor_nombre' => $doctorName,
            'ubicacion' => $appointment->room->name ?? 'Sin asignar',
        ];

        // Notify the assigned doctor
        if ($appointment->doctor_id) {
            self::createNotification($appointment->doctor_id, $message, 'appointment_rescheduled', $data, false);
        }
    }

    /**
     * Create a notification in the database
     */
    private static function createNotification($userId, $message, $type, $data, $isImportant = false)
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'data' => $data,
            'is_read' => false,
            'is_important' => $isImportant,
            'company_id' => $user->company_id ?? null
        ]);
    }

    /**
     * Get all receptionists for the company
     */
    public static function getReceptionists($companyId = null)
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('name', 'receptionist');
        });

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->get();
    }

    /**
     * Notify all receptionists
     */
    private static function notifyReceptionists($message, $type, $data, $isImportant = false)
    {
        $companyId = null;
        
        // Try to get company_id from the data if it contains an appointment
        if (isset($data['appointment_id'])) {
            $companyId = company() ? company()->id : null;
        }
        
        $receptionists = self::getReceptionists($companyId);
        
        foreach ($receptionists as $receptionist) {
            self::createNotification($receptionist->id, $message, $type, $data, $isImportant);
        }
    }
}
