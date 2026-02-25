<?php

namespace App\Observers;

use App\Models\Patient;
use App\Services\ActivityLog\PatientActivityLogService;

class PatientObserver
{
    protected $activityLogService;

    public function __construct()
    {
        $this->activityLogService = new PatientActivityLogService();
    }

    /**
     * Handle the Patient "created" event.
     */
    public function created(Patient $patient): void
    {
        $this->activityLogService->logPatientCreated($patient);
    }

    /**
     * Handle the Patient "updated" event.
     */
    public function updated(Patient $patient): void
    {
        $this->activityLogService->logPatientUpdated($patient);
    }

    /**
     * Handle the Patient "deleted" event.
     */
    public function deleted(Patient $patient): void
    {
        $this->activityLogService->logPatientDeleted($patient);
    }

    /**
     * Handle the Patient "restored" event.
     */
    public function restored(Patient $patient): void
    {
        $this->activityLogService->logPatientRestored($patient);
    }

    /**
     * Handle the Patient "force deleted" event.
     */
    public function forceDeleted(Patient $patient): void
    {
        $this->activityLogService->logPatientForceDeleted($patient);
    }
}
