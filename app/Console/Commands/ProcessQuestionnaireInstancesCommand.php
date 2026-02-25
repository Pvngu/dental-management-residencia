<?php

namespace App\Console\Commands;

use App\Jobs\ProcessQuestionnaireInstances;
use Illuminate\Console\Command;

class ProcessQuestionnaireInstancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'questionnaire:process-instances 
                            {--sync : Run synchronously instead of dispatching job}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process questionnaire instances (auto-close expired, mark assignments, etc.)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing questionnaire instances...');

        try {
            if ($this->option('sync')) {
                // Run synchronously
                $job = new ProcessQuestionnaireInstances();
                $job->handle();
                $this->info('Questionnaire instances processed successfully (sync)');
            } else {
                // Dispatch to queue
                ProcessQuestionnaireInstances::dispatch();
                $this->info('Questionnaire instances processing job dispatched to queue');
            }
        } catch (\Exception $e) {
            $this->error('Error processing questionnaire instances: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
