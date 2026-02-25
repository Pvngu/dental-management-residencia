<?php

namespace App\Jobs;

use App\Models\QuestionnaireInstance;
use App\Models\QuestionnaireAssignment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ProcessQuestionnaireInstances implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * This job handles:
     * 1. Auto-close instances where closes_at has passed
     * 2. Mark EXPIRED assignments for closed instances
     * 3. Calculate scoring snapshots
     * 4. Trigger notifications/reports
     */
    public function handle(): void
    {
        try {
            Log::info('Starting questionnaire instances processing job');

            DB::beginTransaction();

            // 1. Find instances that should be auto-closed
            $instancesToClose = QuestionnaireInstance::where('status', 'OPEN')
                ->where('end_date', '<=', now())
                ->whereNotNull('end_date')
                ->get();

            foreach ($instancesToClose as $instance) {
                Log::info("Auto-closing instance: {$instance->xid}");
                
                // Update instance status
                $instance->update([
                    'status' => 'CLOSED',
                    'end_date' => now()
                ]);

                // Mark pending assignments as expired
                $expiredCount = QuestionnaireAssignment::where('instance_id', $instance->instance_id)
                    ->where('status', 'PENDING')
                    ->update([
                        'status' => 'EXPIRED',
                        'expired_at' => now()
                    ]);

                Log::info("Expired {$expiredCount} assignments for instance {$instance->xid}");

                // Calculate scoring snapshot (placeholder)
                $this->calculateScoringSnapshot($instance);

                // Trigger notifications (placeholder)
                $this->triggerNotifications($instance);
            }

            // 2. Find instances that should be auto-launched
            $instancesToLaunch = QuestionnaireInstance::where('status', 'DRAFT')
                ->where('launch_date', '<=', now())
                ->get();

            foreach ($instancesToLaunch as $instance) {
                Log::info("Auto-launching instance: {$instance->xid}");
                
                $instance->update([
                    'status' => 'OPEN'
                ]);
            }

            DB::commit();

            Log::info('Questionnaire instances processing job completed successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in questionnaire instances processing job: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Calculate scoring snapshot for closed instance
     */
    private function calculateScoringSnapshot(QuestionnaireInstance $instance): void
    {
        try {
            // This would implement the actual scoring calculation
            // For now, just log the action
            Log::info("Calculating scoring snapshot for instance {$instance->xid}");
            
            // Future implementation:
            // 1. Aggregate responses by section/question
            // 2. Calculate scores based on template configuration
            // 3. Store immutable snapshot in questionnaire_scoring_snapshots table
            // 4. Generate summary statistics
            
        } catch (\Exception $e) {
            Log::error("Error calculating scoring snapshot for instance {$instance->xid}: " . $e->getMessage());
        }
    }

    /**
     * Trigger notifications for closed instance
     */
    private function triggerNotifications(QuestionnaireInstance $instance): void
    {
        try {
            // This would implement notification logic
            Log::info("Triggering notifications for instance {$instance->xid}");
            
            // Future implementation:
            // 1. Notify HR/Compliance team
            // 2. Send summary reports
            // 3. Trigger export processes
            // 4. Update dashboards
            
        } catch (\Exception $e) {
            Log::error("Error triggering notifications for instance {$instance->xid}: " . $e->getMessage());
        }
    }
}
