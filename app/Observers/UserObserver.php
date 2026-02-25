<?php

namespace App\Observers;

use App\Classes\Common;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\ActivityLog\UserActivityLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class UserObserver
{
    protected $activityLogService;

    public function __construct()
    {
        $this->activityLogService = new UserActivityLogService();
    }
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->activityLogService->logUserCreated($user);
    }

    public function saving(User $user)
    {
        $company = company();

        // Cannot put in creating, because saving is fired before creating. And we need company id for check bellow
        if ($company && !$company->is_global) {
            $user->company_id = $company->id;
        }
    }

    public function updating(User $user)
    {
        $original = $user->getOriginal();
        if ($user->isDirty('image')) {
            $userImagePath = Common::getFolderPath('userImagePath');

            File::delete($userImagePath . $original['image']);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        $this->activityLogService->logUserUpdated($user);
    }

    public function deleting(User $user)
    {
        $userImagePath = Common::getFolderPath('userImagePath');

        File::delete($userImagePath . $user->image);
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        $this->activityLogService->logUserDeleted($user);
    }

    /**
     * Handle the User "restored" event for soft deletes.
     */
    public function restored(User $user): void
    {
        $this->activityLogService->logUserRestored($user);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        $this->activityLogService->logUserForceDeleted($user);
    }
}
