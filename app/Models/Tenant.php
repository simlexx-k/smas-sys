<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Hashids\Hashids;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = ['domain', 'name'];

    private static $hashids;

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

    public function admin()
    {
        return $this->hasOne(User::class, 'tenant_id')->where('role', 'tenant-admin');
    }

    public function classes()
    {
        return $this->hasMany(\App\Models\SchoolClass::class);
    }
}
