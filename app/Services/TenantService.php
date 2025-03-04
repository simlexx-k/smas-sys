<?php

namespace App\Services;

use App\Models\Tenant;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TenantService
{
    public function getUsageStats(Tenant $tenant): array
    {
        $storageUsed = Storage::size("tenants/{$tenant->id}") ?? 0;
        $storageLimit = config('tenant.storage_limit', 1024 * 1024 * 1024); // 1GB default

        $activeUsers = $tenant->users()->where('is_active', true)->count();
        $totalUsers = $tenant->users()->count();

        $activeStudents = $tenant->students()->where('status', 'active')->count();
        $totalStudents = $tenant->students()->count();

        return [
            'storage' => [
                'used' => $storageUsed,
                'total' => $storageLimit,
                'percentage' => $storageLimit > 0 ? round(($storageUsed / $storageLimit) * 100) : 0
            ],
            'users' => [
                'active' => $activeUsers,
                'total' => $totalUsers,
                'percentage' => $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0
            ],
            'students' => [
                'active' => $activeStudents,
                'total' => $totalStudents,
                'percentage' => $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100) : 0
            ],
            'last_activity' => $tenant->last_activity_at
        ];
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

    public function getClassStats(Tenant $tenant): array
    {
        $classes = $tenant->classes;
        $totalStudents = $tenant->students()->count();
        $averageClassSize = $classes->count() > 0 
            ? round($totalStudents / $classes->count(), 1) 
            : 0;

        return [
            'total_classes' => $classes->count(),
            'total_students' => $totalStudents,
            'average_class_size' => $averageClassSize,
            'classes_with_teachers' => $tenant->classes()->has('teacher')->count(),
            'active_classes' => $tenant->classes()->where('is_active', true)->count()
        ];
    }

    public function getStudentStats(Tenant $tenant): array
    {
        $totalStudents = $tenant->students()->count();
        $activeStudents = $tenant->students()->where('status', 'active')->count();
        
        return [
            'total' => $totalStudents,
            'active' => $activeStudents,
            'with_guardians' => $tenant->students()->has('guardians')->count(),
            'attendance_rate' => $this->calculateAttendanceRate($tenant),
            'gender_distribution' => [
                'male' => $tenant->students()->where('gender', 'male')->count(),
                'female' => $tenant->students()->where('gender', 'female')->count()
            ]
        ];
    }

    private function calculateAttendanceRate(Tenant $tenant): float
    {
        $lastMonth = now()->subMonth();
        $attendances = $tenant->attendances()
            ->where('date', '>=', $lastMonth)
            ->get();

        if ($attendances->isEmpty()) {
            return 0;
        }

        $present = $attendances->where('status', 'present')->count();
        return round(($present / $attendances->count()) * 100, 1);
    }
} 