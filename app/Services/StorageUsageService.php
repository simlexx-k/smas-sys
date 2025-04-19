<?php

namespace App\Services;

use App\Models\Tenant;

class StorageUsageService
{
    public function updateUsage(Tenant $tenant, int $fileSizeMB): void
    {
        $tenant->usage()->update([
            'storage_used' => $tenant->usage->storage_used + $fileSizeMB,
            'file_count' => $tenant->usage->file_count + 1,
            'last_activity' => now()
        ]);
    }

    public function getFormattedUsage(Tenant $tenant): string
    {
        $usage = $tenant->usage;
        return "{$usage->storage_used}MB / 5120MB (" . 
            number_format(($usage->storage_used / 5120) * 100, 1) . "%)";
    }
}