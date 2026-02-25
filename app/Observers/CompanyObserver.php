<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\ActivityLog;
use App\Services\ActivityLog\CompanyActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CompanyObserver
{
    protected $activityLogService;

    public function __construct()
    {
        $this->activityLogService = new CompanyActivityLogService();
    }
    /**
     * Handle the Company "created" event.
     */
    public function created(Company $company): void
    {
        $this->activityLogService->logCompanyCreated($company);
    }

    /**
     * Handle the Company "updated" event.
     */
    public function updated(Company $company): void
    {
        $this->activityLogService->logCompanyUpdated($company);
    }

    /**
     * Handle the Company "deleted" event.
     */
    public function deleted(Company $company): void
    {
        $this->activityLogService->logCompanyDeleted($company);
    }

    /**
     * Handle the Company "restored" event for soft deletes.
     */
    public function restored(Company $company): void
    {
        $this->activityLogService->logCompanyRestored($company);
    }

    /**
     * Handle the Company "force deleted" event.
     */
    public function forceDeleted(Company $company): void
    {
        $this->activityLogService->logCompanyForceDeleted($company);
    }
}
