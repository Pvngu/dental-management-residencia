<?php

namespace App\Services\ActivityLog;

use App\Models\PatientFile;

class PatientFileActivityLogService extends BaseActivityLogService
{
    /**
     * Log patient file upload
     */
    public function logPatientFileCreated(PatientFile $patientFile)
    {
        $patientName = $this->getPatientName($patientFile->patient_id);
        $fileName = $patientFile->file ?? 'Unknown file';
        $fileType = $patientFile->file_type ?? pathinfo($fileName, PATHINFO_EXTENSION) ?? 'Unknown';
        
        return $this->logActivity(
            'CREATED',
            'patient_files',
            ActivityLogMessageProvider::getLocalizedMessage('file_uploaded', [
                'patient_name' => $patientName,
                'file_name' => $fileName,
                'file_type' => strtoupper($fileType)
            ]),
            $patientFile->company_id
        );
    }

    /**
     * Log patient file deletion
     */
    public function logPatientFileDeleted(PatientFile $patientFile)
    {
        $patientName = $this->getPatientName($patientFile->patient_id);
        $fileName = $patientFile->file ?? 'Unknown file';
        
        return $this->logActivity(
            'DELETED',
            'patient_files',
            ActivityLogMessageProvider::getLocalizedMessage('file_deleted', [
                'patient_name' => $patientName,
                'file_name' => $fileName
            ]),
            $patientFile->company_id
        );
    }
}