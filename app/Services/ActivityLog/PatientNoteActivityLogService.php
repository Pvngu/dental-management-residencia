<?php

namespace App\Services\ActivityLog;

use App\Models\PatientNote;

class PatientNoteActivityLogService extends BaseActivityLogService
{
    /**
     * Log patient note creation
     */
    public function logPatientNoteCreated(PatientNote $patientNote)
    {
        $patientName = $this->getPatientName($patientNote->patient_id);
        $preview = $this->truncateText($patientNote->description, 50);
        $noteType = $patientNote->note_type ?? 'General';
        
        return $this->logActivity(
            'CREATED',
            'patient_notes',
            ActivityLogMessageProvider::getLocalizedMessage('note_added', [
                'patient_name' => $patientName,
                'note_preview' => $preview,
                'note_type' => $noteType
            ]),
            $patientNote->company_id,
            $patientNote->patient_id
        );
    }

    /**
     * Log patient note update
     */
    public function logPatientNoteUpdated(PatientNote $patientNote)
    {
        $changes = $this->formatChanges($patientNote);
        
        if (!empty($changes)) {
            $patientName = $this->getPatientName($patientNote->patient_id);
            $preview = $this->truncateText($patientNote->description, 50);
            
            return $this->logActivity(
                'UPDATED',
                'patient_notes',
                ActivityLogMessageProvider::getLocalizedMessage('note_updated', [
                    'patient_name' => $patientName,
                    'note_preview' => $preview,
                    'changes' => $changes
                ]),
                $patientNote->company_id,
                $patientNote->patient_id
            );
        }
        
        return null;
    }

    /**
     * Log patient note deletion
     */
    public function logPatientNoteDeleted(PatientNote $patientNote)
    {
        $patientName = $this->getPatientName($patientNote->patient_id);
        $preview = $this->truncateText($patientNote->description, 50);
        $noteType = $patientNote->note_type ?? 'General';
        
        return $this->logActivity(
            'DELETED',
            'patient_notes',
            ActivityLogMessageProvider::getLocalizedMessage('note_deleted', [
                'patient_name' => $patientName,
                'note_preview' => $preview,
                'note_type' => $noteType
            ]),
            $patientNote->company_id,
            $patientNote->patient_id
        );
    }
}