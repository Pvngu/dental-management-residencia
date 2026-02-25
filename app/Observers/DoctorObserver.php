<?php

namespace App\Observers;

use App\Models\Doctor;
use App\Models\ActivityLog;
use App\Services\ActivityLog\DoctorActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DoctorObserver
{
    protected $activityLogService;

    public function __construct()
    {
        $this->activityLogService = new DoctorActivityLogService();
    }
    /**
     * Handle the Doctor "created" event.
     */
    public function created(Doctor $doctor): void
    {
        $this->activityLogService->logDoctorCreated($doctor);
    }

    /**
     * Handle the Doctor "updated" event.
     */
    public function updated(Doctor $doctor): void
    {
        $this->activityLogService->logDoctorUpdated($doctor);
    }

    /**
     * Handle the Doctor "deleted" event.
     */
    public function deleted(Doctor $doctor): void
    {
        $this->activityLogService->logDoctorDeleted($doctor);
    }

    /**
     * Handle the Doctor "restored" event for soft deletes.
     */
    public function restored(Doctor $doctor): void
    {
        $this->activityLogService->logDoctorRestored($doctor);
    }

    /**
     * Handle the Doctor "force deleted" event.
     */
    public function forceDeleted(Doctor $doctor): void
    {
        $this->activityLogService->logDoctorForceDeleted($doctor);
    }
}
