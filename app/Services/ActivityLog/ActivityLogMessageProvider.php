<?php

namespace App\Services\ActivityLog;

use App\Models\Lang;

class ActivityLogMessageProvider
{
    /**
     * Get localized activity message based on company language
     */
    public static function getLocalizedMessage(string $key, array $replacements = [])
    {
        $company = company();
        $langKey = 'en'; // default language
        
        if ($company && $company->lang_id) {
            $lang = Lang::find($company->lang_id);
            if ($lang) {
                $langKey = $lang->key;
            }
        }

        $messages = self::getActivityMessages();
        $template = $messages[$langKey][$key] ?? $messages['en'][$key] ?? $key;
        
        // Replace placeholders with actual values
        foreach ($replacements as $placeholder => $value) {
            $template = str_replace('{' . $placeholder . '}', $value, $template);
        }
        
        return $template;
    }

    /**
     * Get all activity messages in different languages
     */
    private static function getActivityMessages()
    {
        return [
            'en' => [
                // Patient messages
                'patient_created' => 'Patient created: {patient_name} (ID: {patient_id})',
                'patient_updated' => 'Patient updated: {patient_name} - Modified: {changes}',
                'patient_deleted' => 'Patient deleted: {patient_name} (ID: {patient_id})',
                'patient_restored' => 'Patient restored: {patient_name} (ID: {patient_id})',
                'patient_force_deleted' => 'Patient permanently deleted: {patient_name} (ID: {patient_id})',

                // Patient Note messages
                'note_added' => 'Note added to {patient_name}: "{note_preview}" ({note_type})',
                'note_updated' => 'Note updated for {patient_name}: "{note_preview}" - Changes: {changes}',
                'note_deleted' => 'Note deleted from {patient_name}: "{note_preview}" ({note_type})',

                // Appointment messages
                'appointment_created' => 'Appointment scheduled for {patient_name} on {appointment_date} with {doctor_name}',
                'appointment_updated' => 'Appointment modified for {patient_name} - Changes: {changes}',
                'appointment_deleted' => 'Appointment cancelled for {patient_name} on {appointment_date}',

                // Address messages
                'address_added' => 'Address added for {patient_name}: {address}',
                'address_updated' => 'Address updated for {patient_name}: {address} - Changes: {changes}',
                'address_deleted' => 'Address removed from {patient_name}: {address}',

                // File messages
                'file_uploaded' => 'File uploaded for {patient_name}: {file_name} ({file_type})',
                'file_deleted' => 'File deleted from {patient_name}: {file_name}',

                // User messages
                'user_created_detailed' => 'New user registered: {userName} (ID: {userId})',
                'user_updated_detailed' => 'User information updated: {userName} (ID: {userId}) - {changes}',
                'user_deleted_detailed' => 'User deleted: {userName} (ID: {userId})',
                'user_restored_detailed' => 'User restored: {userName} (ID: {userId})',
                'user_force_deleted_detailed' => 'User permanently deleted: {userName} (ID: {userId})',

                // Doctor messages
                'doctor_created_detailed' => 'New doctor registered: {doctorName} (ID: {doctorId})',
                'doctor_updated_detailed' => 'Doctor information updated: {doctorName} (ID: {doctorId}) - {changes}',
                'doctor_deleted_detailed' => 'Doctor deleted: {doctorName} (ID: {doctorId})',
                'doctor_restored_detailed' => 'Doctor restored: {doctorName} (ID: {doctorId})',
                'doctor_force_deleted_detailed' => 'Doctor permanently deleted: {doctorName} (ID: {doctorId})',

                // Company messages
                'company_created_detailed' => 'New company created: {companyName}',
                'company_updated_detailed' => 'Company information updated: {companyName} - {changes}',
                'company_deleted_detailed' => 'Company deleted: {companyName}',
                'company_restored_detailed' => 'Company restored: {companyName}',
                'company_force_deleted_detailed' => 'Company permanently deleted: {companyName}',

                // Patient Message messages
                'message_sent' => 'Message sent to {patient_name} via {channel} by {user_name}: "{message_preview}"',
                'message_received' => 'Message received from {patient_name} via {channel}: "{message_preview}"',
                'message_status_updated' => 'Message status updated for {patient_name} ({channel}): {old_status} → {new_status} - "{message_preview}"',
                'message_read' => 'Message read by {patient_name} ({channel}): "{message_preview}"',
                'message_updated' => 'Message updated for {patient_name} ({channel}): "{message_preview}" - Changes: {changes}',
                'message_failed' => 'Message failed to send to {patient_name} via {channel}: "{message_preview}" - Error: {error}',
                'message_deleted' => 'Message deleted for {patient_name} ({channel}): "{message_preview}"',
                'conversation_started' => 'Conversation started with {patient_name} via {channel} (initiated by {initiated_by})',
                'bulk_message_sent' => 'Bulk message sent by {user_name} to {patient_count} patients via {channel}: "{message_preview}"',

                // OpenCase Messages
                'open_case_created' => 'Open case "{title}" was created with {priority} priority for patient {patient_name}',
                'open_case_updated' => 'Open case "{title}" was updated for patient {patient_name}',
                'open_case_deleted' => 'Open case "{title}" was deleted for patient {patient_name}',
                'open_case_restored' => 'Open case "{title}" was restored for patient {patient_name}',
                'open_case_force_deleted' => 'Open case "{title}" was permanently deleted for patient {patient_name}',
                'open_case_status_changed' => 'Open case "{title}" status changed from {old_status} to {new_status} for patient {patient_name}',
                'open_case_priority_changed' => 'Open case "{title}" priority changed from {old_priority} to {new_priority} for patient {patient_name}',

                // Generic field changes
                'field_changed' => '{field}: "{old_value}" → "{new_value}"',
                'multiple_changes' => '{count} fields modified',
            ],
            'es' => [
                // Patient messages
                'patient_created' => 'Paciente creado: {patient_name} (ID: {patient_id})',
                'patient_updated' => 'Paciente actualizado: {patient_name} - Modificado: {changes}',
                'patient_deleted' => 'Paciente eliminado: {patient_name} (ID: {patient_id})',
                'patient_restored' => 'Paciente restaurado: {patient_name} (ID: {patient_id})',
                'patient_force_deleted' => 'Paciente eliminado permanentemente: {patient_name} (ID: {patient_id})',

                // Patient Note messages
                'note_added' => 'Nota agregada a {patient_name}: "{note_preview}" ({note_type})',
                'note_updated' => 'Nota actualizada para {patient_name}: "{note_preview}" - Cambios: {changes}',
                'note_deleted' => 'Nota eliminada de {patient_name}: "{note_preview}" ({note_type})',

                // Appointment messages
                'appointment_created' => 'Cita programada para {patient_name} el {appointment_date} con {doctor_name}',
                'appointment_updated' => 'Cita modificada para {patient_name} - Cambios: {changes}',
                'appointment_deleted' => 'Cita cancelada para {patient_name} el {appointment_date}',

                // Address messages
                'address_added' => 'Dirección agregada para {patient_name}: {address}',
                'address_updated' => 'Dirección actualizada para {patient_name}: {address} - Cambios: {changes}',
                'address_deleted' => 'Dirección eliminada de {patient_name}: {address}',

                // File messages
                'file_uploaded' => 'Archivo subido para {patient_name}: {file_name} ({file_type})',
                'file_deleted' => 'Archivo eliminado de {patient_name}: {file_name}',

                // User messages
                'user_created_detailed' => 'Nuevo usuario registrado: {userName} (ID: {userId})',
                'user_updated_detailed' => 'Información del usuario actualizada: {userName} (ID: {userId}) - {changes}',
                'user_deleted_detailed' => 'Usuario eliminado: {userName} (ID: {userId})',
                'user_restored_detailed' => 'Usuario restaurado: {userName} (ID: {userId})',
                'user_force_deleted_detailed' => 'Usuario eliminado permanentemente: {userName} (ID: {userId})',

                // Doctor messages
                'doctor_created_detailed' => 'Nuevo doctor registrado: {doctorName} (ID: {doctorId})',
                'doctor_updated_detailed' => 'Información del doctor actualizada: {doctorName} (ID: {doctorId}) - {changes}',
                'doctor_deleted_detailed' => 'Doctor eliminado: {doctorName} (ID: {doctorId})',
                'doctor_restored_detailed' => 'Doctor restaurado: {doctorName} (ID: {doctorId})',
                'doctor_force_deleted_detailed' => 'Doctor eliminado permanentemente: {doctorName} (ID: {doctorId})',

                // Company messages
                'company_created_detailed' => 'Nueva compañía creada: {companyName}',
                'company_updated_detailed' => 'Información de la compañía actualizada: {companyName} - {changes}',
                'company_deleted_detailed' => 'Compañía eliminada: {companyName}',
                'company_restored_detailed' => 'Compañía restaurada: {companyName}',
                'company_force_deleted_detailed' => 'Compañía eliminada permanentemente: {companyName}',

                // Patient Message messages
                'message_sent' => 'Mensaje enviado a {patient_name} vía {channel} por {user_name}: "{message_preview}"',
                'message_received' => 'Mensaje recibido de {patient_name} vía {channel}: "{message_preview}"',
                'message_status_updated' => 'Estado del mensaje actualizado para {patient_name} ({channel}): {old_status} → {new_status} - "{message_preview}"',
                'message_read' => 'Mensaje leído por {patient_name} ({channel}): "{message_preview}"',
                'message_updated' => 'Mensaje actualizado para {patient_name} ({channel}): "{message_preview}" - Cambios: {changes}',
                'message_failed' => 'Envío de mensaje falló a {patient_name} vía {channel}: "{message_preview}" - Error: {error}',
                'message_deleted' => 'Mensaje eliminado para {patient_name} ({channel}): "{message_preview}"',
                'conversation_started' => 'Conversación iniciada con {patient_name} vía {channel} (iniciado por {initiated_by})',
                'bulk_message_sent' => 'Mensaje masivo enviado por {user_name} a {patient_count} pacientes vía {channel}: "{message_preview}"',

                // OpenCase Messages
                'open_case_created' => 'Caso abierto "{title}" fue creado con prioridad {priority} para el paciente {patient_name}',
                'open_case_updated' => 'Caso abierto "{title}" fue actualizado para el paciente {patient_name}',
                'open_case_deleted' => 'Caso abierto "{title}" fue eliminado para el paciente {patient_name}',
                'open_case_restored' => 'Caso abierto "{title}" fue restaurado para el paciente {patient_name}',
                'open_case_force_deleted' => 'Caso abierto "{title}" fue eliminado permanentemente para el paciente {patient_name}',
                'open_case_status_changed' => 'Estado del caso abierto "{title}" cambió de {old_status} a {new_status} para el paciente {patient_name}',
                'open_case_priority_changed' => 'Prioridad del caso abierto "{title}" cambió de {old_priority} a {new_priority} para el paciente {patient_name}',

                // Generic field changes
                'field_changed' => '{field}: "{old_value}" → "{new_value}"',
                'multiple_changes' => '{count} campos modificados',
            ]
        ];
    }
}