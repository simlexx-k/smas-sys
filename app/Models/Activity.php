<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    public static function log($type, $action, $description, $subject = null, $metadata = null)
    {
        return static::create([
            'user_id' => auth()->id(),
            'tenant_id' => tenant()?->id,  // Add tenant_id if in tenant context
            'type' => $type,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
            'metadata' => $metadata
        ]);
    }
} 