<?php

namespace App\Console\Commands;

use App\Services\SubscriptionRenewalService;
use Illuminate\Console\Command;

class ProcessSubscriptionRenewals extends Command
{
    protected $signature = 'subscriptions:process-renewals';
    protected $description = 'Process subscription renewals and send notifications';

    public function handle(SubscriptionRenewalService $renewalService): int
    {
        $this->info('Processing subscription renewals...');
        $renewalService->processRenewals();
        $this->info('Finished processing renewals.');
        return Command::SUCCESS;
    }
} 