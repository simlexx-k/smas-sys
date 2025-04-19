<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantUsage extends Model
{
    protected $fillable = [
        'tenant_id',
        'storage_used', // in megabytes
        'file_count',
        'last_activity'
    ];

    protected $casts = [
        'last_activity' => 'datetime',
        'storage_used' => 'integer',
        'file_count' => 'integer'
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
} 