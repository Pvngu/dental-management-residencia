<?php

namespace App\Observers;

use App\Models\OpenCase;
use App\Models\OpenCaseHistory;
use App\Services\ActivityLog\OpenCaseActivityLogService;
use Illuminate\Support\Facades\Auth;

class OpenCaseObserver
{
    protected $openCaseActivityLogService;

    public function __construct(OpenCaseActivityLogService $openCaseActivityLogService)
    {
        $this->openCaseActivityLogService = $openCaseActivityLogService;
    }
    /**
     * Handle the OpenCase "created" event.
     */
    public function created(OpenCase $openCase): void
    {
        // Log activity
        $this->openCaseActivityLogService->logOpenCaseCreated($openCase);
        
        // Create history record
        $this->createHistory($openCase, 'created', null, null, null);
    }

    /**
     * Handle the OpenCase "updated" event.
     */
    public function updated(OpenCase $openCase): void
    {
        // Log activity for updates
        $this->openCaseActivityLogService->logOpenCaseUpdated($openCase);
        
        $changes = $openCase->getChanges();
        
        // Don't track updated_at changes
        unset($changes['updated_at']);
        
        foreach ($changes as $field => $newValue) {
            $oldValue = $openCase->getOriginal($field);
            
            // Log specific activity for status and priority changes
            if ($field === 'status') {
                $this->openCaseActivityLogService->logOpenCaseStatusChanged($openCase, $oldValue, $newValue);
            } elseif ($field === 'priority') {
                $this->openCaseActivityLogService->logOpenCasePriorityChanged($openCase, $oldValue, $newValue);
            }
            
            // Determine action based on field changes
            $action = 'updated';
            if ($field === 'status') {
                $action = 'status_changed';
                
                // Special actions for specific status changes
                if ($newValue === 'resolved') {
                    $action = 'resolved';
                } elseif ($oldValue === 'closed' && $newValue === 'open') {
                    $action = 'reopened';
                }
            } elseif ($field === 'priority') {
                $action = 'priority_changed';
            }
            
            $this->createHistory($openCase, $action, $field, $oldValue, $newValue);
        }
    }

    /**
     * Handle the OpenCase "deleted" event.
     */
    public function deleted(OpenCase $openCase): void
    {
        // Log activity
        $this->openCaseActivityLogService->logOpenCaseDeleted($openCase);
        
        // Create history record
        $this->createHistory($openCase, 'deleted', null, null, null);
    }

    /**
     * Handle the OpenCase "restored" event.
     */
    public function restored(OpenCase $openCase): void
    {
        // Log activity
        $this->openCaseActivityLogService->logOpenCaseRestored($openCase);
        
        // Create history record
        $this->createHistory($openCase, 'restored', null, null, null);
    }

    /**
     * Create a history record
     */
    private function createHistory($openCase, $action, $fieldName, $oldValue, $newValue)
    {
        OpenCaseHistory::create([
            'open_case_id' => $openCase->id,
            'action' => $action,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'user_id' => Auth::id(),
            'company_id' => $openCase->company_id,
        ]);
    }
}
