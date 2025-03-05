<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredTenants extends Command
{
    protected $signature = 'tenants:cleanup-expired';
    protected $description = 'Permanently delete tenants that have been in trash for more than 30 days';

    public function handle()
    {
        $this->info('Starting cleanup of expired tenants...');

        $expiredTenants = Tenant::onlyTrashed()
            ->where('deleted_at', '<=', now()->subDays(30))
            ->get();

        $count = 0;
        foreach ($expiredTenants as $tenant) {
            try {
                $tenant->forceDelete();
                $count++;
                $this->info("Permanently deleted tenant: {$tenant->name}");
            } catch (\Exception $e) {
                Log::error("Failed to delete tenant {$tenant->id}: " . $e->getMessage());
                $this->error("Failed to delete tenant {$tenant->name}");
            }
        }

        $this->info("Cleanup completed. Deleted {$count} expired tenants.");
    }
} 