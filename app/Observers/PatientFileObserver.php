<?php

namespace App\Observers;

use App\Models\PatientFile;
use App\Models\PatientHistory;
use App\Services\ActivityLog\PatientFileActivityLogService;

class PatientFileObserver
{
    protected $activityLogService;

    public function __construct()
    {
        $this->activityLogService = new PatientFileActivityLogService();
    }

    public function saving(PatientFile $patientFile)
    {
        $company = company();

        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if ($company && !$company->is_global) {
            $patientFile->company_id = company()->id;
        }
    }

    /**
     * Handle the PatientFile "created" event.
     */
    public function created(PatientFile $patientFile): void
    {
        // Create patient history entry
        $data = [
            'file_id' => $patientFile->id,
            'file_name' => $patientFile->file_name,
            'file_type' => $patientFile->file_type,
            'file_size' => $patientFile->file_size,
            'uploaded_by' => $patientFile->uploaded_by,
        ];

        PatientHistory::createEntry(
            $patientFile->patient_id,
            'file_uploaded',
            $data,
            $patientFile->uploaded_by,
            $patientFile
        );

        $this->activityLogService->logPatientFileCreated($patientFile);
    }

    /**
     * Handle the PatientFile "deleted" event.
     */
    public function deleted(PatientFile $patientFile): void
    {
        // Create patient history entry
        $data = [
            'file_id' => $patientFile->id,
            'file_name' => $patientFile->file_name,
            'file_type' => $patientFile->file_type,
            'deleted_at' => now(),
        ];

        PatientHistory::createEntry(
            $patientFile->patient_id,
            'file_deleted',
            $data,
            null,
            $patientFile
        );

        $this->activityLogService->logPatientFileDeleted($patientFile);
    }
}
