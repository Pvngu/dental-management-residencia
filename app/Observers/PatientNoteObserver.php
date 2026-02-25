<?php

namespace App\Observers;

use App\Models\PatientNote;
use App\Models\PatientHistory;
use App\Services\ActivityLog\PatientNoteActivityLogService;
use Illuminate\Support\Facades\Auth;

class PatientNoteObserver
{
    protected $activityLogService;

    public function __construct()
    {
        $this->activityLogService = new PatientNoteActivityLogService();
    }

    /**
     * Handle the PatientNote "created" event.
     */
    public function created(PatientNote $patientNote): void
    {
        $data = [
            'note_id' => $patientNote->id,
            'note_type' => $patientNote->note_type,
            'content' => $patientNote->content,
            'is_private' => $patientNote->is_private,
            'is_highlighted' => $patientNote->is_highlighted,
            'related_type' => $patientNote->related_type,
            'related_id' => $patientNote->related_id,
            'created_by' => $patientNote->user_id,
        ];

        PatientHistory::createEntry(
            $patientNote->patient_id,
            'note_added',
            $data,
            $patientNote->user_id,
            $patientNote
        );

        $this->activityLogService->logPatientNoteCreated($patientNote);
    }

    /**
     * Handle the PatientNote "updated" event.
     */
    public function updated(PatientNote $patientNote): void
    {
        $original = $patientNote->getOriginal();
        $changes = [];

        foreach ($patientNote->getDirty() as $key => $value) {
            if (in_array($key, ['updated_at'])) {
                continue;
            }

            $changes[$key] = [
                'old' => array_key_exists($key, $original) ? $original[$key] : null,
                'new' => $value
            ];
        }

        if (!empty($changes)) {
            $data = [
                'note_id' => $patientNote->id,
                'changes' => $changes,
                'current_content' => $patientNote->content,
                'modified_by' => $patientNote->user_id,
            ];

            PatientHistory::createEntry(
                $patientNote->patient_id,
                'note_updated',
                $data,
                $patientNote->user_id,
                $patientNote
            );

            $this->activityLogService->logPatientNoteUpdated($patientNote);
        }
    }

    /**
     * Handle the PatientNote "deleted" event.
     */
    public function deleted(PatientNote $patientNote): void
    {
        $currentUserId = Auth::check() ? Auth::id() : null;
        
        $data = [
            'note_id' => $patientNote->id,
            'note_type' => $patientNote->note_type,
            'content' => $patientNote->content,
            'deleted_by' => $currentUserId,
            'deleted_at' => now(),
        ];

        PatientHistory::createEntry(
            $patientNote->patient_id,
            'note_deleted',
            $data,
            $currentUserId,
            $patientNote
        );

        $this->activityLogService->logPatientNoteDeleted($patientNote);
    }
}
