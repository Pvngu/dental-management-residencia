<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait ActivityLogTrait
{
    /**
     * Log an activity
     *
     * @param string $action
     * @param string|null $entity
     * @param string|null $description
     * @param array|null $user
     * @param array|null $jsonLog
     * @return ActivityLog
     */
    public function logActivity(
        string $action,
        string $entity = null,
        string $description = null,
        array $user = null,
        array $jsonLog = null
    ) {
        $data = [
            'action' => $action,
            'entity' => $entity,
            'description' => $description,
            'company_id' => Auth::check() && Auth::user()->company_id ? Auth::user()->company_id : null,
        ];

        if ($user) {
            $data['user'] = $user;
        } elseif (Auth::check()) {
            $data['user'] = [
                'id' => Auth::user()->id,
                'name' => Auth::user()->name,
                'email' => Auth::user()->email,
                'role' => Auth::user()->role_id ? Auth::user()->role->name : null,
            ];
        }

        if ($jsonLog) {
            $data['json_log'] = $jsonLog;
        }

        return ActivityLog::create($data);
    }

    /**
     * Log user login activity
     *
     * @param array|null $additionalData
     * @return ActivityLog
     */
    public function logLogin(array $additionalData = null)
    {
        return $this->logActivity(
            'login',
            'User',
            'User logged in',
            null,
            $additionalData
        );
    }

    /**
     * Log user logout activity
     *
     * @param array|null $additionalData
     * @return ActivityLog
     */
    public function logLogout(array $additionalData = null)
    {
        return $this->logActivity(
            'logout',
            'User',
            'User logged out',
            null,
            $additionalData
        );
    }

    /**
     * Log model created activity
     *
     * @param object $model
     * @param array|null $additionalData
     * @return ActivityLog
     */
    public function logCreated($model, array $additionalData = null)
    {
        $className = class_basename($model);
        
        return $this->logActivity(
            'created',
            $className,
            "{$className} was created",
            null,
            array_merge([
                'model_id' => $model->id ?? null,
                'model_data' => method_exists($model, 'toArray') ? $model->toArray() : null,
            ], $additionalData ?? [])
        );
    }

    /**
     * Log model updated activity
     *
     * @param object $model
     * @param array|null $originalData
     * @param array|null $additionalData
     * @return ActivityLog
     */
    public function logUpdated($model, array $originalData = null, array $additionalData = null)
    {
        $className = class_basename($model);
        
        return $this->logActivity(
            'updated',
            $className,
            "{$className} was updated",
            null,
            array_merge([
                'model_id' => $model->id ?? null,
                'original_data' => $originalData,
                'updated_data' => method_exists($model, 'toArray') ? $model->toArray() : null,
            ], $additionalData ?? [])
        );
    }

    /**
     * Log model deleted activity
     *
     * @param object $model
     * @param array|null $additionalData
     * @return ActivityLog
     */
    public function logDeleted($model, array $additionalData = null)
    {
        $className = class_basename($model);
        
        return $this->logActivity(
            'deleted',
            $className,
            "{$className} was deleted",
            null,
            array_merge([
                'model_id' => $model->id ?? null,
                'model_data' => method_exists($model, 'toArray') ? $model->toArray() : null,
            ], $additionalData ?? [])
        );
    }
}
