<?php

namespace App\Http\Controllers\Example;

/**
 * Example usage of PatientMessageActivityLogService
 * 
 * This file demonstrates how to use the PatientMessageActivityLogService
 * in your controllers or other services to log message-related activities.
 */

class PatientMessageExampleController
{
    use App\Services\ActivityLog\PatientMessageActivityLogService;

    /**
     * Example: Sending a single message
     */
    public function sendSingleMessage($patientId, $messageContent, $channel = 'sms')
    {
        $activityService = new PatientMessageActivityLogService();
        
        // Create the message record
        $message = PatientMessage::create([
            'patient_id' => $patientId,
            'company_id' => company()->id,
            'sent_by_user_id' => auth()->id(),
            'message' => $messageContent,
            'direction' => 'outbound',
            'status' => 'pending',
            'phone_number' => '+1234567890', // Get from patient
            'channel' => $channel,
            'metadata' => [
                'sent_from' => 'web_interface',
                'priority' => 'normal'
            ]
        ]);

        // The observer will automatically log the message_sent activity
        // But you can also manually log additional activities:
        
        // If message status changes to sent:
        $message->update(['status' => 'sent', 'sent_at' => now()]);
        // Observer logs: message_status_updated activity
        
        // If message gets delivered:
        $message->update(['status' => 'delivered', 'delivered_at' => now()]);
        // Observer logs: message_status_updated activity
        
        return $message;
    }

    /**
     * Example: Receiving an inbound message
     */
    public function receiveMessage($patientId, $messageContent, $channel = 'sms')
    {
        // Create inbound message
        $message = PatientMessage::create([
            'patient_id' => $patientId,
            'company_id' => company()->id,
            'sent_by_user_id' => null, // Inbound messages don't have a sender user
            'message' => $messageContent,
            'direction' => 'inbound',
            'status' => 'received',
            'phone_number' => '+1234567890', // Patient's phone
            'channel' => $channel,
            'metadata' => [
                'received_from' => $channel . '_webhook',
                'auto_reply_eligible' => true
            ]
        ]);

        // Observer automatically logs: message_received activity
        
        return $message;
    }

    /**
     * Example: Bulk messaging
     */
    public function sendBulkMessage(array $patientIds, $messageContent, $channel = 'sms')
    {
        $activityService = new PatientMessageActivityLogService();
        $messages = [];
        
        foreach ($patientIds as $patientId) {
            $messages[] = PatientMessage::create([
                'patient_id' => $patientId,
                'company_id' => company()->id,
                'sent_by_user_id' => auth()->id(),
                'message' => $messageContent,
                'direction' => 'outbound',
                'status' => 'pending',
                'phone_number' => $this->getPatientPhone($patientId),
                'channel' => $channel
            ]);
        }

        // Log bulk operation
        $activityService->logBulkMessageSent(
            $patientIds,
            company()->id,
            $channel,
            substr($messageContent, 0, 50),
            auth()->id()
        );

        return $messages;
    }

    /**
     * Example: Start a conversation
     */
    public function startConversation($patientId, $channel = 'whatsapp', $initiatedBy = 'user')
    {
        $activityService = new PatientMessageActivityLogService();
        
        // Log conversation start
        $activityService->logConversationStarted(
            $patientId,
            company()->id,
            $channel,
            $initiatedBy
        );

        // Then send initial message...
        $this->sendSingleMessage(
            $patientId,
            "Hello! This is " . company()->name . ". How can we help you today?",
            $channel
        );
    }

    /**
     * Example: Mark message as read
     */
    public function markMessageAsRead($messageId)
    {
        $message = PatientMessage::find($messageId);
        
        if ($message && $message->direction === 'inbound' && !$message->read_at) {
            // Use the model method to mark as read
            $message->markAsRead();
            // Observer automatically logs: message_read activity
        }

        return $message;
    }

    /**
     * Example: Handle message failure
     */
    public function handleMessageFailure($messageId, $errorMessage)
    {
        $message = PatientMessage::find($messageId);
        
        if ($message) {
            $message->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $errorMessage
            ]);
            // Observer automatically logs: message_failed activity
        }

        return $message;
    }

    /**
     * Helper method to get patient phone number
     */
    private function getPatientPhone($patientId)
    {
        // Implement your logic to get patient phone number
        // This is just a placeholder
        return '+1234567890';
    }
}

/*
ACTIVITY LOG EXAMPLES:

The service will generate activities like these:

English:
- "Message sent to John Doe via SMS by Dr. Smith: "Hi, your appointment is confirmed for tomorrow at 10:00 AM""
- "Message received from Jane Smith via WhatsApp: "Thank you for the reminder""
- "Message status updated for Bob Johnson (SMS): Pending → Delivered - "Please bring your insurance card""
- "Message read by Alice Brown (WhatsApp): "Your test results are ready""
- "Bulk message sent by Dr. Smith to 15 patients via SMS: "Reminder: Our clinic will be closed tomorrow""
- "Conversation started with Mike Wilson via WhatsApp (initiated by clinic)"

Spanish:
- "Mensaje enviado a Juan Pérez vía SMS por Dr. García: "Hola, su cita está confirmada para mañana a las 10:00 AM""
- "Mensaje recibido de María González vía WhatsApp: "Gracias por el recordatorio""
- "Estado del mensaje actualizado para Carlos López (SMS): Pendiente → Entregado - "Por favor traiga su tarjeta de seguro""
- "Mensaje leído por Ana Martín (WhatsApp): "Sus resultados están listos""
- "Mensaje masivo enviado por Dr. García a 15 pacientes vía SMS: "Recordatorio: Nuestra clínica estará cerrada mañana""
- "Conversación iniciada con Miguel Torres vía WhatsApp (iniciado por clínica)"

*/