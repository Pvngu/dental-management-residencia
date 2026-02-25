<?php

namespace App\Services\ActivityLog;

use App\Models\Doctor;

class DoctorActivityLogService extends BaseActivityLogService
{
    /**
     * Log doctor creation
     */
    public function logDoctorCreated(Doctor $doctor)
    {
        $doctorName = $this->getDoctorName($doctor->id);
        
        return $this->logActivity(
            'CREATED',
            'doctors',
            ActivityLogMessageProvider::getLocalizedMessage('doctor_created_detailed', [
                'doctorName' => $doctorName,
                'doctorId' => $doctor->id
            ]),
            $doctor->company_id
        );
    }

    /**
     * Log doctor update
     */
    public function logDoctorUpdated(Doctor $doctor)
    {
        $changes = $this->formatChanges($doctor);
        
        if (!empty($changes)) {
            $doctorName = $this->getDoctorName($doctor->id);
            
            return $this->logActivity(
                'UPDATED',
                'doctors',
                ActivityLogMessageProvider::getLocalizedMessage('doctor_updated_detailed', [
                    'doctorName' => $doctorName,
                    'doctorId' => $doctor->id,
                    'changes' => $changes
                ]),
                $doctor->company_id
            );
        }
        
        return null;
    }

    /**
     * Log doctor deletion
     */
    public function logDoctorDeleted(Doctor $doctor)
    {
        $doctorName = $this->getDoctorName($doctor->id);
        
        return $this->logActivity(
            'DELETED',
            'doctors',
            ActivityLogMessageProvider::getLocalizedMessage('doctor_deleted_detailed', [
                'doctorName' => $doctorName,
                'doctorId' => $doctor->id
            ]),
            $doctor->company_id
        );
    }

    /**
     * Log doctor restoration
     */
    public function logDoctorRestored(Doctor $doctor)
    {
        $doctorName = $this->getDoctorName($doctor->id);
        
        return $this->logActivity(
            'RESTORED',
            'doctors',
            ActivityLogMessageProvider::getLocalizedMessage('doctor_restored_detailed', [
                'doctorName' => $doctorName,
                'doctorId' => $doctor->id
            ]),
            $doctor->company_id
        );
    }

    /**
     * Log doctor force deletion
     */
    public function logDoctorForceDeleted(Doctor $doctor)
    {
        $doctorName = $this->getDoctorName($doctor->id);
        
        return $this->logActivity(
            'FORCE_DELETED',
            'doctors',
            ActivityLogMessageProvider::getLocalizedMessage('doctor_force_deleted_detailed', [
                'doctorName' => $doctorName,
                'doctorId' => $doctor->id
            ]),
            $doctor->company_id
        );
    }
}