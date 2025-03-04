<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Hashids\Hashids;
use App\Traits\LogsActivity;

class Tenant extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'domain', 
        'name',
        'address',
        'phone',
        'email',
        'logo_url',
        'school_type',
        'status',
        'subscription_plan',
        'subscription_ends_at',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array',
        'subscription_ends_at' => 'datetime',
    ];

    private static $hashids;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    protected static function booted()
    {
        static::$hashids = new Hashids('a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6', 8);
    }

    public static function current()
    {
        $subdomain = explode('.', request()->getHost())[0];
        return static::where('domain', $subdomain)->first();
    }

    public function getHashedIdAttribute()
    {
        return static::$hashids->encode($this->id);
    }

    public static function findByHashedId($hashedId)
    {
        $decoded = static::$hashids->decode($hashedId);
        return $decoded ? static::find($decoded[0]) : null;
    }

    public static function decodeHashedId($hashedId)
    {
        if (!static::$hashids) {
            static::$hashids = new Hashids('a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6', 8);
        }
        $decoded = static::$hashids->decode($hashedId);
        \Log::info("Decoded value: " . json_encode($decoded));
        return $decoded[0] ?? null;
    }

    public function scopeFilter(Builder $query, ?string $domain = null, ?string $name = null): Builder
    {
        if ($domain) {
            $query->where('domain', 'like', '%' . $domain . '%');
        }

        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }

        return $query;
    }

    // New scope for active tenants
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // Check if tenant is active
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    // Check if subscription is valid
    public function hasValidSubscription(): bool
    {
        return $this->subscription_ends_at === null || 
               $this->subscription_ends_at->isFuture();
    }

    public function admin()
    {
        return $this->hasOne(User::class, 'tenant_id')->where('role', 'tenant-admin');
    }

    public function classes()
    {
        return $this->hasMany(\App\Models\SchoolClass::class);
    }

    // Get logo URL or default
    public function getLogoUrlAttribute($value)
    {
        return $value ?? '/images/default-school-logo.png';
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription && $this->subscription->isActive();
    }

    public function isTrialing(): bool
    {
        return $this->subscription && $this->subscription->isTrialing();
    }

    public function subscriptionIsExpiringSoon(): bool
    {
        return $this->subscription && $this->subscription->isExpiringSoon();
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }
}
