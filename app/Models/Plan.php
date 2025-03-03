<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;

class Plan extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'billing_period',
        'trial_period_days',
        'features',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'features' => 'array',
        'is_active' => 'boolean',
        'trial_period_days' => 'integer',
        'sort_order' => 'integer',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }
} 