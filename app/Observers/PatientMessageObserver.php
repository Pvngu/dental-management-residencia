<?php

namespace App\Observers;

use App\Models\PatientMessage;
use App\Services\ActivityLog\PatientMessageActivityLogService;

class PatientMessageObserver
{
    protected $activityLogService;

    public function __construct()
    {
        $this->activityLogService = new PatientMessageActivityLogService();
    }

    /**
     * Handle the PatientMessage "created" event.
     */
    public function created(PatientMessage $message): void
    {
        // Log based on message direction
        if ($message->direction === 'inbound') {
            $this->activityLogService->logMessageReceived($message);
        } else {
            $this->activityLogService->logMessageSent($message);
        }
    }

    /**
     * Handle the PatientMessage "updated" event.
     */
    public function updated(PatientMessage $message): void
    {
        $changes = $message->getDirty();
        
        if (empty($changes)) {
            return;
        }

        // Check for specific status changes that warrant special logging
        if (isset($changes['status']) && $changes['status'] === 'failed') {
            $this->activityLogService->logMessageFailed($message);
        } elseif (isset($changes['read_at']) && $changes['read_at'] !== null) {
            // Message was just marked as read
            $this->activityLogService->logMessageStatusUpdated($message);
        } elseif (isset($changes['status'])) {
            // General status update (sent, delivered, etc.)
            $this->activityLogService->logMessageStatusUpdated($message);
        } else {
            // Other updates
            $this->activityLogService->logMessageStatusUpdated($message);
        }
    }

    /**
     * Handle the PatientMessage "deleted" event.
     */
    public function deleted(PatientMessage $message): void
    {
        // We might want to log message deletions differently
        // For now, we'll use a simple approach
        $this->activityLogService->logActivity(
            'DELETED',
            'patient_messages',
            \App\Services\ActivityLog\ActivityLogMessageProvider::getLocalizedMessage('message_deleted', [
                'patient_name' => $this->activityLogService->getPatientName($message->patient_id),
                'channel' => ucfirst($message->channel),
                'message_preview' => $this->activityLogService->truncateText($message->message, 50)
            ]),
            $message->company_id,
            json_encode([
                'patient_id' => $message->patient_id,
                'message_id' => $message->id,
                'channel' => $message->channel,
                'direction' => $message->direction,
                'deleted_at' => now()
            ])
        );
    }
}