<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class Activity extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'type',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime'
    ];

    protected $with = ['user']; // Always load the user relationship

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    // Helper method to log activity
    public static function log($type, $action, $description, $subject = null, $metadata = [], $tenantId = null)
    {
        return static::create([
            'user_id' => auth()->id(),
            'tenant_id' => $tenantId,
            'type' => $type,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'metadata' => $metadata
        ]);
    }

    public static function logTenantDeletion(Tenant $tenant, array $metadata = [])
    {
        $defaultMetadata = [
            'subscription_status' => $tenant->subscription?->status,
            'storage_used' => 0, // Set default value
            'deleted_at' => now()->toDateTimeString(),
            'deleted_by' => auth()->user()->name
        ];

        // Only try to get storage size if directory exists
        try {
            if (Storage::directoryExists("tenants/{$tenant->id}")) {
                $defaultMetadata['storage_used'] = Storage::size("tenants/{$tenant->id}");
            }
        } catch (\Exception $e) {
            // Log the error but continue with the deletion
            Log::warning("Could not determine storage size for tenant {$tenant->id}: {$e->getMessage()}");
        }

        return static::log(
            'tenant',
            'delete',
            "Deleted school: {$tenant->name}",
            $tenant,
            array_merge($defaultMetadata, $metadata),
            $tenant->id
        );
    }
} 