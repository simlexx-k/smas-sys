<?php

namespace App\Jobs;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Spatie\Multitenancy\Jobs\TenantAware;

class CleanupTenantData implements ShouldQueue, TenantAware
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 3600; // 1 hour

    private $tenantId;

    public function __construct($tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function handle()
    {
        Log::info("Starting cleanup for tenant: {$this->tenantId}");

        try {
            // Delete tenant files
            Storage::deleteDirectory("tenants/{$this->tenantId}");

            // Delete tenant logo
            $tenant = Tenant::find($this->tenantId);
            if ($tenant && $tenant->logo_url) {
                Storage::disk('public')->delete($tenant->logo_url);
            }

            // Archive important data
            $this->archiveTenantData();

            Log::info("Completed cleanup for tenant: {$this->tenantId}");
        } catch (\Exception $e) {
            Log::error("Error cleaning up tenant data: " . $e->getMessage());
            throw $e;
        }
    }

    protected function archiveTenantData()
    {
        $tenant = Tenant::find($this->tenantId);
        $archiveData = [
            'tenant' => $tenant->toArray(),
            'subscription' => $tenant->subscription?->toArray(),
            'admin' => $tenant->admin?->toArray(),
            'deleted_at' => now()->toDateTimeString(),
            'deleted_by' => auth()->user()->name
        ];

        Storage::put(
            "archives/tenants/{$this->tenantId}.json",
            json_encode($archiveData, JSON_PRETTY_PRINT)
        );
    }

    public function getTenantId(): int
    {
        return $this->tenantId;
    }
} 