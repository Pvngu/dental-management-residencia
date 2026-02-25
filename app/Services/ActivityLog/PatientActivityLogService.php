<?php

namespace App\Services\ActivityLog;

use App\Models\Patient;

class PatientActivityLogService extends BaseActivityLogService
{
    /**
     * Log patient creation
     */
    public function logPatientCreated(Patient $patient)
    {
        $patientName = $this->getPatientName($patient->id);
        
        return $this->logActivity(
            'CREATED',
            'patients',
            ActivityLogMessageProvider::getLocalizedMessage('patient_created', [
                'patient_name' => $patientName,
                'patient_id' => $patient->id
            ]),
            $patient->company_id,
            $patient->id
        );
    }

    /**
     * Log patient update
     */
    public function logPatientUpdated(Patient $patient)
    {
        $changes = $this->formatChanges($patient);
        
        if (!empty($changes)) {
            $patientName = $this->getPatientName($patient->id);
            
            return $this->logActivity(
                'UPDATED',
                'patients',
                ActivityLogMessageProvider::getLocalizedMessage('patient_updated', [
                    'patient_name' => $patientName,
                    'changes' => $changes
                ]),
                $patient->company_id,
                $patient->id
            );
        }
        
        return null;
    }

    /**
     * Log patient deletion
     */
    public function logPatientDeleted(Patient $patient)
    {
        $patientName = $this->getPatientName($patient->id);
        
        return $this->logActivity(
            'DELETED',
            'patients',
            ActivityLogMessageProvider::getLocalizedMessage('patient_deleted', [
                'patient_name' => $patientName,
                'patient_id' => $patient->id
            ]),
            $patient->company_id,
            $patient->id
        );
    }

    /**
     * Log patient restoration
     */
    public function logPatientRestored(Patient $patient)
    {
        $patientName = $this->getPatientName($patient->id);
        
        return $this->logActivity(
            'RESTORED',
            'patients',
            ActivityLogMessageProvider::getLocalizedMessage('patient_restored', [
                'patient_name' => $patientName,
                'patient_id' => $patient->id
            ]),
            $patient->company_id,
            $patient->id
        );
    }

    /**
     * Log patient force deletion
     */
    public function logPatientForceDeleted(Patient $patient)
    {
        $patientName = $this->getPatientName($patient->id);
        
        return $this->logActivity(
            'FORCE_DELETED',
            'patients',
            ActivityLogMessageProvider::getLocalizedMessage('patient_force_deleted', [
                'patient_name' => $patientName,
                'patient_id' => $patient->id
            ]),
            $patient->company_id,
            $patient->id
        );
    }
}