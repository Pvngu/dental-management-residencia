<?php

namespace App\Services\ActivityLog;

use App\Models\User;

class UserActivityLogService extends BaseActivityLogService
{
    /**
     * Log user creation
     */
    public function logUserCreated(User $user)
    {
        $userName = $this->getUserName($user->id);
        
        return $this->logActivity(
            'CREATED',
            'users',
            ActivityLogMessageProvider::getLocalizedMessage('user_created_detailed', [
                'userName' => $userName,
                'userId' => $user->id
            ]),
            $user->company_id
        );
    }

    /**
     * Log user update
     */
    public function logUserUpdated(User $user)
    {
        $changes = $this->formatChanges($user);
        
        if (!empty($changes)) {
            $userName = $this->getUserName($user->id);
            
            return $this->logActivity(
                'UPDATED',
                'users',
                ActivityLogMessageProvider::getLocalizedMessage('user_updated_detailed', [
                    'userName' => $userName,
                    'userId' => $user->id,
                    'changes' => $changes
                ]),
                $user->company_id
            );
        }
        
        return null;
    }

    /**
     * Log user deletion
     */
    public function logUserDeleted(User $user)
    {
        $userName = $this->getUserName($user->id);
        
        return $this->logActivity(
            'DELETED',
            'users',
            ActivityLogMessageProvider::getLocalizedMessage('user_deleted_detailed', [
                'userName' => $userName,
                'userId' => $user->id
            ]),
            $user->company_id
        );
    }

    /**
     * Log user restoration
     */
    public function logUserRestored(User $user)
    {
        $userName = $this->getUserName($user->id);
        
        return $this->logActivity(
            'RESTORED',
            'users',
            ActivityLogMessageProvider::getLocalizedMessage('user_restored_detailed', [
                'userName' => $userName,
                'userId' => $user->id
            ]),
            $user->company_id
        );
    }

    /**
     * Log user force deletion
     */
    public function logUserForceDeleted(User $user)
    {
        $userName = $this->getUserName($user->id);
        
        return $this->logActivity(
            'FORCE_DELETED',
            'users',
            ActivityLogMessageProvider::getLocalizedMessage('user_force_deleted_detailed', [
                'userName' => $userName,
                'userId' => $user->id
            ]),
            $user->company_id
        );
    }
}