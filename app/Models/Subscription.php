<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Activity;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\InvoiceController;

class Subscription extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'price',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'renewed_at',
        'canceled_at'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'renewed_at' => 'datetime',
        'canceled_at' => 'datetime'
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_EXPIRED = 'expired';
    const STATUS_CANCELED = 'canceled';
    const STATUS_TRIAL = 'trial';

    protected static function booted()
    {
        static::created(function ($subscription) {
            if ($subscription->status === 'active') {
                app(InvoiceController::class)->generateForSubscription($subscription);
            }
        });

        static::updated(function ($subscription) {
            // Only generate if relevant fields change
            if ($subscription->isDirty(['starts_at', 'ends_at', 'price', 'status', 'plan_id'])) {
                // Regenerate invoices only if still active
                if ($subscription->status === 'active') {
                    // Delete existing invoices for the changed period
                    $subscription->invoices()
                        ->whereBetween('billing_period_end', [
                            $subscription->getOriginal('starts_at'),
                            $subscription->getOriginal('ends_at')
                        ])
                        ->delete();

                    // Generate new invoices
                    app(InvoiceController::class)->generateForSubscription($subscription);
                }
            }
        });
    }

    public function renew($duration = 30)
    {
        $now = now();
        $newEndsAt = $this->ends_at && $this->ends_at->isFuture() 
            ? $this->ends_at->addDays($duration)
            : $now->addDays($duration);

        DB::transaction(function () use ($now, $newEndsAt) {
            $this->update([
                'status' => self::STATUS_ACTIVE,
                'renewed_at' => $now,
                'ends_at' => $newEndsAt,
                'canceled_at' => null
            ]);

            // Generate new invoice
            app(InvoiceController::class)->generateForSubscription($this);

            Activity::log(
                'subscription',
                'renew',
                "Subscription renewed for {$this->tenant->name}",
                $this,
                [
                    'previous_end_date' => $this->getOriginal('ends_at'),
                    'new_end_date' => $newEndsAt,
                    'duration' => $duration
                ]
            );
        });

        return $this;
    }

    public function cancel($immediate = false)
    {
        if ($immediate) {
            $this->update([
                'status' => self::STATUS_CANCELED,
                'ends_at' => now(),
                'cancels_at' => now()
            ]);
        } else {
            $this->update([
                'cancels_at' => now()
            ]);
        }

        Activity::log(
            'subscription',
            'cancel',
            "Subscription canceled for {$this->tenant->name}",
            $this,
            [
                'immediate' => $immediate,
                'end_date' => $this->ends_at
            ]
        );

        return $this;
    }

    public function getStatus(): string
    {
        $now = now();

        if ($this->status === self::STATUS_CANCELED) {
            return 'canceled';
        }

        if ($this->trial_ends_at && $this->trial_ends_at->isFuture()) {
            return 'trial';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'expired';
        }

        if ($this->canceled_at && $this->ends_at && $this->ends_at->isFuture()) {
            return 'canceling';
        }

        if ($this->ends_at && $this->ends_at->subDays(30)->isPast()) {
            return 'expiring_soon';
        }

        return 'active';
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && 
            ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function isTrialing(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function isExpiringSoon(): bool
    {
        return $this->ends_at && 
            $this->ends_at->isFuture() && 
            $this->ends_at->subDays(30)->isPast();
    }

    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    public function isCanceled(): bool
    {
        return $this->status === self::STATUS_CANCELED || 
            ($this->cancels_at && $this->ends_at && $this->ends_at->isPast());
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function getBillingPeriods(): array
    {
        $periods = [];
        $current = $this->starts_at->copy();
        
        while ($current < $this->ends_at) {
            $periodEnd = $current->copy()->addMonth();
            if ($periodEnd > $this->ends_at) {
                $periodEnd = $this->ends_at;
            }
            
            $periods[] = [
                'start' => $current,
                'end' => $periodEnd,
                'due_date' => $current->addDays(7)
            ];
            
            $current = $periodEnd;
        }
        
        return $periods;
    }
} 