<?php

namespace App\Console\Commands;

use App\Http\Controllers\DashboardController;
use Illuminate\Console\Command;

class LogTenantStats extends Command
{
    protected $signature = 'tenants:log-stats';
    protected $description = 'Log tenant subscription statistics';

    public function handle()
    {
        $controller = app(DashboardController::class);
        $controller->index(request());
        
        $this->info('Tenant statistics have been logged.');
    }
} 