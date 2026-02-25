<?php

namespace App\Services\ActivityLog;

class OpenCaseActivityLogService extends BaseActivityLogService
{
    public function logOpenCaseCreated($openCase)
    {
        $description = ActivityLogMessageProvider::getLocalizedMessage(
            'open_case_created',
            [
                'title' => $openCase->title,
                'priority' => $openCase->priority,
                'patient_name' => $openCase->patient->user->name ?? 'Unknown Patient'
            ]
        );

        return $this->logActivity(
            'CREATED',
            'open_cases',
            $description,
            $openCase->company_id,
            $openCase->patient_id
        );
    }

    public function logOpenCaseUpdated($openCase)
    {
        $description = ActivityLogMessageProvider::getLocalizedMessage(
            'open_case_updated',
            [
                'title' => $openCase->title,
                'patient_name' => $openCase->patient->user->name ?? 'Unknown Patient'
            ]
        );

        return $this->logActivity(
            'UPDATED',
            'open_cases',
            $description,
            $openCase->company_id,
            $openCase->patient_id
        );
    }

    public function logOpenCaseDeleted($openCase)
    {
        $description = ActivityLogMessageProvider::getLocalizedMessage(
            'open_case_deleted',
            [
                'title' => $openCase->title,
                'patient_name' => $openCase->patient->user->name ?? 'Unknown Patient'
            ]
        );

        return $this->logActivity(
            'DELETED',
            'open_cases',
            $description,
            $openCase->company_id,
            $openCase->patient_id
        );
    }

    public function logOpenCaseRestored($openCase)
    {
        $description = ActivityLogMessageProvider::getLocalizedMessage(
            'open_case_restored',
            [
                'title' => $openCase->title,
                'patient_name' => $openCase->patient->user->name ?? 'Unknown Patient'
            ]
        );

        return $this->logActivity(
            'RESTORED',
            'open_cases',
            $description,
            $openCase->company_id,
            $openCase->patient_id
        );
    }

    public function logOpenCaseForceDeleted($openCase)
    {
        $description = ActivityLogMessageProvider::getLocalizedMessage(
            'open_case_force_deleted',
            [
                'title' => $openCase->title,
                'patient_name' => $openCase->patient->user->name ?? 'Unknown Patient'
            ]
        );

        return $this->logActivity(
            'FORCE_DELETED',
            'open_cases',
            $description,
            $openCase->company_id,
            $openCase->patient_id
        );
    }

    public function logOpenCaseStatusChanged($openCase, $oldStatus, $newStatus)
    {
        $description = ActivityLogMessageProvider::getLocalizedMessage(
            'open_case_status_changed',
            [
                'title' => $openCase->title,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'patient_name' => $openCase->patient->user->name ?? 'Unknown Patient'
            ]
        );

        return $this->logActivity(
            'UPDATED',
            'open_cases',
            $description,
            $openCase->company_id,
            $openCase->patient_id
        );
    }

    public function logOpenCasePriorityChanged($openCase, $oldPriority, $newPriority)
    {
        $description = ActivityLogMessageProvider::getLocalizedMessage(
            'open_case_priority_changed',
            [
                'title' => $openCase->title,
                'old_priority' => $oldPriority,
                'new_priority' => $newPriority,
                'patient_name' => $openCase->patient->user->name ?? 'Unknown Patient'
            ]
        );

        return $this->logActivity(
            'UPDATED',
            'open_cases',
            $description,
            $openCase->company_id,
            $openCase->patient_id
        );
    }
}