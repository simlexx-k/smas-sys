<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantService
{
    public function getUsageStats(Tenant $tenant): array
    {
        try {
            // Check if tenant directory exists
            $tenantPath = "tenants/{$tenant->id}";
            if (!Storage::exists($tenantPath)) {
                return [
                    'storage_used' => '0 B',
                    'file_count' => 0,
                    'last_activity' => $tenant->updated_at?->diffForHumans() ?? 'Never'
                ];
            }

            // Get all files recursively
            $files = Storage::allFiles($tenantPath);
            
            $totalSize = 0;
            foreach ($files as $file) {
                try {
                    $totalSize += Storage::size($file);
                } catch (\Exception $e) {
                    continue;
                }
            }

            return [
                'storage_used' => $this->formatSize($totalSize),
                'file_count' => count($files),
                'last_activity' => $tenant->updated_at?->diffForHumans() ?? 'Never'
            ];
        } catch (\Exception $e) {
            return [
                'storage_used' => '0 B',
                'file_count' => 0,
                'last_activity' => $tenant->updated_at?->diffForHumans() ?? 'Never'
            ];
        }
    }

    public function getTenantStats(Tenant $tenant): array
    {
        return [
            'subscription_status' => $tenant->subscription?->status ?? 'No subscription',
            'subscription_ends' => $tenant->subscription?->ends_at?->format('Y-m-d') ?? 'N/A',
            'created_at' => $tenant->created_at->format('Y-m-d'),
            'domain' => $tenant->domain,
            'admin_email' => $tenant->admin?->email ?? 'No admin',
            'is_active' => $tenant->status === 'active'
        ];
    }

    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    public function create(array $data): Tenant
    {
        // Generate a unique subdomain from the school name
        $domain = Str::slug($data['name']);
        $counter = 1;
        while (Tenant::where('domain', $domain)->exists()) {
            $domain = Str::slug($data['name']) . '-' . $counter++;
        }

        // Handle logo upload if present
        if (isset($data['logo']) && $data['logo']) {
            $data['logo_url'] = $data['logo']->store('tenant-logos', 'public');
        }

        // Create the tenant
        $tenant = Tenant::create([
            'name' => $data['name'],
            'domain' => $domain,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'logo_url' => $data['logo_url'] ?? null,
            'status' => Tenant::STATUS_ACTIVE,
            'settings' => [
                'timezone' => config('app.timezone'),
                'date_format' => 'Y-m-d',
                'currency' => 'USD'
            ]
        ]);

        // Create necessary directories
        Storage::makeDirectory("tenants/{$tenant->id}");
        Storage::makeDirectory("tenants/{$tenant->id}/uploads");

        return $tenant;
    }

    public function update(Tenant $tenant, array $data): Tenant
    {
        // Handle logo upload if present
        if (isset($data['logo']) && $data['logo']) {
            // Delete old logo if exists
            if ($tenant->logo_url) {
                Storage::disk('public')->delete($tenant->logo_url);
            }
            $data['logo_url'] = $data['logo']->store('tenant-logos', 'public');
        }

        $tenant->update([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'logo_url' => $data['logo_url'] ?? $tenant->logo_url,
            'status' => $data['is_active'] ? Tenant::STATUS_ACTIVE : Tenant::STATUS_INACTIVE
        ]);

        return $tenant->fresh();
    }

    public function delete(Tenant $tenant): void
    {
        // Delete tenant's storage
        Storage::deleteDirectory("tenants/{$tenant->id}");

        // Delete logo if exists
        if ($tenant->logo_url) {
            Storage::disk('public')->delete($tenant->logo_url);
        }

        // Delete the tenant (soft delete)
        $tenant->delete();
    }
} 