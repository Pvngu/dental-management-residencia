<?php

namespace App\Services\ActivityLog;

use App\Models\PatientMessage;

class PatientMessageActivityLogService extends BaseActivityLogService
{
    /**
     * Log when a message is sent to a patient
     */
    public function logMessageSent(PatientMessage $message)
    {
        $patientName = $this->getPatientName($message->patient_id);
        $userName = $message->sent_by_user_id ? $this->getUserName($message->sent_by_user_id) : 'System';
        $messagePreview = $this->truncateText($message->message, 50);
        $channelDisplay = ucfirst($message->channel);
        
        $this->logActivity(
            'CREATED',
            'patient_messages',
            ActivityLogMessageProvider::getLocalizedMessage('message_sent', [
                'patient_name' => $patientName,
                'user_name' => $userName,
                'channel' => $channelDisplay,
                'message_preview' => $messagePreview
            ]),
            $message->company_id,
            $message->patient_id,
            json_encode([
                'patient_id' => $message->patient_id,
                'message_id' => $message->id,
                'direction' => $message->direction,
                'channel' => $message->channel,
                'phone_number' => $message->phone_number,
                'status' => $message->status,
                'sent_by' => $message->sent_by_user_id
            ])
        );
    }

    /**
     * Log when a message is received from a patient
     */
    public function logMessageReceived(PatientMessage $message)
    {
        $patientName = $this->getPatientName($message->patient_id);
        $messagePreview = $this->truncateText($message->message, 50);
        $channelDisplay = ucfirst($message->channel);
        
        $this->logActivity(
            'CREATED',
            'patient_messages',
            ActivityLogMessageProvider::getLocalizedMessage('message_received', [
                'patient_name' => $patientName,
                'channel' => $channelDisplay,
                'message_preview' => $messagePreview
            ]),
            $message->company_id,
            $message->patient_id,
            json_encode([
                'patient_id' => $message->patient_id,
                'message_id' => $message->id,
                'direction' => $message->direction,
                'channel' => $message->channel,
                'phone_number' => $message->phone_number,
                'status' => $message->status
            ])
        );
    }

    /**
     * Log when a message status is updated (delivered, read, failed)
     */
    public function logMessageStatusUpdated(PatientMessage $message)
    {
        $changes = $message->getDirty();
        
        if (empty($changes)) {
            return;
        }

        $patientName = $this->getPatientName($message->patient_id);
        $messagePreview = $this->truncateText($message->message, 50);
        $channelDisplay = ucfirst($message->channel);
        
        // Determine what changed
        if (isset($changes['status'])) {
            $originalStatus = $message->getOriginal('status');
            $newStatus = $changes['status'];
            
            $messageKey = 'message_status_updated';
            $params = [
                'patient_name' => $patientName,
                'channel' => $channelDisplay,
                'message_preview' => $messagePreview,
                'old_status' => ucfirst($originalStatus),
                'new_status' => ucfirst($newStatus)
            ];
        } elseif (isset($changes['read_at']) && $changes['read_at'] !== null) {
            $messageKey = 'message_read';
            $params = [
                'patient_name' => $patientName,
                'channel' => $channelDisplay,
                'message_preview' => $messagePreview
            ];
        } else {
            // General update
            $messageKey = 'message_updated';
            $params = [
                'patient_name' => $patientName,
                'channel' => $channelDisplay,
                'message_preview' => $messagePreview,
                'changes' => $this->formatChanges($changes)
            ];
        }
        
        $this->logActivity(
            'UPDATED',
            'patient_messages',
            ActivityLogMessageProvider::getLocalizedMessage($messageKey, $params),
            $message->company_id,
            $message->patient_id,
            json_encode([
                'patient_id' => $message->patient_id,
                'message_id' => $message->id,
                'changes' => $changes,
                'channel' => $message->channel,
                'direction' => $message->direction
            ])
        );
    }

    /**
     * Log when a message fails to send
     */
    public function logMessageFailed(PatientMessage $message)
    {
        $patientName = $this->getPatientName($message->patient_id);
        $messagePreview = $this->truncateText($message->message, 50);
        $channelDisplay = ucfirst($message->channel);
        $errorMessage = $message->error_message ?? 'Unknown error';
        
        $this->logActivity(
            'ERROR',
            'patient_messages',
            ActivityLogMessageProvider::getLocalizedMessage('message_failed', [
                'patient_name' => $patientName,
                'channel' => $channelDisplay,
                'message_preview' => $messagePreview,
                'error' => $errorMessage
            ]),
            $message->company_id,
            $message->patient_id,
            json_encode([
                'patient_id' => $message->patient_id,
                'message_id' => $message->id,
                'channel' => $message->channel,
                'error_message' => $errorMessage,
                'failed_at' => $message->failed_at
            ])
        );
    }

    /**
     * Log when a message conversation is started
     */
    public function logConversationStarted($patientId, $companyId, $channel, $initiatedBy = 'user')
    {
        $patientName = $this->getPatientName($patientId);
        $channelDisplay = ucfirst($channel);
        
        $this->logActivity(
            'CREATED',
            'patient_messages',
            ActivityLogMessageProvider::getLocalizedMessage('conversation_started', [
                'patient_name' => $patientName,
                'channel' => $channelDisplay,
                'initiated_by' => $initiatedBy === 'patient' ? 'patient' : 'clinic'
            ]),
            $companyId,
            $patientId,
            json_encode([
                'patient_id' => $patientId,
                'channel' => $channel,
                'initiated_by' => $initiatedBy,
                'action' => 'conversation_started'
            ])
        );
    }

    /**
     * Log bulk message sending activity
     */
    public function logBulkMessageSent($patientIds, $companyId, $channel, $messagePreview, $sentByUserId = null)
    {
        $patientCount = count($patientIds);
        $userName = $sentByUserId ? $this->getUserName($sentByUserId) : 'System';
        $channelDisplay = ucfirst($channel);
        
        $this->logActivity(
            'BULK_OPERATION',
            'patient_messages',
            ActivityLogMessageProvider::getLocalizedMessage('bulk_message_sent', [
                'user_name' => $userName,
                'patient_count' => $patientCount,
                'channel' => $channelDisplay,
                'message_preview' => $messagePreview
            ]),
            $companyId,
            json_encode([
                'patient_ids' => $patientIds,
                'patient_count' => $patientCount,
                'channel' => $channel,
                'sent_by' => $sentByUserId,
                'message_preview' => $messagePreview
            ])
        );
    }
}