<?php

namespace App\Console\Commands;

use App\Services\Platform\SubscriptionLifecycleService;
use Illuminate\Console\Command;

class ProcessSubscriptionLifecycleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-lifecycle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process subscription trial expirations, grace periods, and renewal lifecycles.';

    /**
     * Execute the console command.
     */
    public function handle(SubscriptionLifecycleService $service): int
    {
        $this->info('Starting subscription lifecycle processing...');

        $results = $service->processAll();

        $this->table(
            ['Metric / Action', 'Count'],
            [
                ['Trial Transitions to Grace Period', $results['trial_to_grace']],
                ['Trial Expirations Finalized', $results['trial_expired']],
                ['Renewal Overdue Transitions to Grace Period', $results['renewal_to_grace']],
                ['Renewal Expirations Finalized', $results['renewal_expired']],
                ['Upcoming Renewal Notices Dispatched', $results['renewal_notices']],
                ['Total Lifecycle Actions Taken', $results['total_actions']],
            ]
        );

        $this->info("Subscription lifecycle processing finished. Total actions: {$results['total_actions']}");

        return Command::SUCCESS;
    }
}

