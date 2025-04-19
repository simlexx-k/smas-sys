<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User;
use Hashids\Hashids;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;
use App\Jobs\CleanupTenantData;
use App\Notifications\TenantDeleted;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use SoftDeletes, LogsActivity;

    protected $fillable = [
        'domain', 
        'name',
        'address',
        'phone',
        'email',
        'logo_path',
        'school_type',
        'status',
        'subscription_plan',
        'subscription_ends_at',
        'settings',
        'storage_used',
    ];

    protected $casts = [
        'settings' => 'array',
        'subscription_ends_at' => 'datetime',
    ];

    private static $hashids;

    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    public static $currentModel;

    protected static function booted()
    {
        static::$hashids = new Hashids('a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6q7r8s9t0u1v2w3x4y5z6', 8);

        static::deleting(function ($tenant) {
            Activity::logTenantDeletion($tenant);
            
            if (!$tenant->isForceDeleting()) {
                // Only dispatch cleanup job for soft deletes
                CleanupTenantData::dispatch($tenant->id)
                    ->delay(now()->addMinutes(5));
            }
        });

        // Temporarily disable automatic activity logging for tenant creation
        // static::created(function ($tenant) {
        //     static::logActivity('create', 'Tenant Created', "New tenant '{$tenant->name}' was created");
        // });

        static::created(function ($tenant) {
            $tenant->usage()->create([
                'storage_used' => 0,
                'file_count' => 0
            ]);
        });
    }

    public function getFormattedAddressAttribute() 
    {
        return nl2br(e($this->address));
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
        return $this->status === 'active' && $this->hasActiveSubscription();
    }

    // Check if subscription is valid
    public function hasValidSubscription(): bool
    {
        return $this->subscription_ends_at === null || 
               $this->subscription_ends_at->isFuture();
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id')->where('role', User::ROLE_TENANT_ADMIN);
    }

    public function classes()
    {
        return $this->hasMany(\App\Models\SchoolClass::class);
    }

    // Get logo URL or default
    public function getLogoUrlAttribute()
    {
        if ($this->logo_path) {
            // Sanitize the path by removing quotes and invalid characters
            $cleanPath = trim($this->logo_path, "\"'");
            \Log::channel('tenant')->debug('Sanitized logo path', [
                'original' => $this->logo_path,
                'clean' => $cleanPath
            ]);
            
            return Storage::disk('tenant')->url($cleanPath);
        }
        
        return '/images/default-school-logo.png';
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
        return $this->subscription()
            ->where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->exists();
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

    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->subscription) {
                    return 'inactive';
                }

                if ($this->subscription->status === Subscription::STATUS_ACTIVE
                    && (!$this->subscription->ends_at || $this->subscription->ends_at > now())
                    && !$this->subscription->cancels_at) {
                    return 'active';
                }

                return 'inactive';
            }
        );
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($tenant) {
            // Only allow active status if there's a valid subscription
            if ($tenant->status === 'active') {
                $hasActiveSubscription = $tenant->subscription()
                    ->where('status', 'active')
                    ->where(function ($query) {
                        $query->whereNull('ends_at')
                            ->orWhere('ends_at', '>', now());
                    })
                    ->exists();

                if (!$hasActiveSubscription) {
                    $tenant->status = 'inactive';
                }
            }
        });

        static::restored(function ($tenant) {
            Activity::log(
                'tenant',
                'restore',
                "Restored school: {$tenant->name}",
                $tenant
            );
        });
    }

    public function canBeRestored(): bool
    {
        return $this->deleted_at && 
               $this->deleted_at->addDays(30)->isFuture();
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function teacherUsers()
    {
        return $this->users()->where('role', User::ROLE_TEACHER);
    }

    // Add this method to get all classes for a specific teacher
    public function getTeacherClasses(User $teacher)
    {
        return $this->classes()
            ->whereHas('teachers', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->teacher->id);
            })
            ->with(['students'])
            ->get();
    }

    // Add this method to get upcoming lessons for a teacher
    public function getTeacherUpcomingLessons(User $teacher)
    {
        return $this->classes()
            ->whereHas('teachers', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->teacher->id);
            })
            ->with(['lessons' => function ($query) {
                $query->where('start_time', '>', now())
                      ->orderBy('start_time', 'asc')
                      ->limit(5);
            }])
            ->get()
            ->pluck('lessons')
            ->flatten();
    }

    // Add this method to set user context
    public static function setUserContext($userId)
    {
        static::$currentModel = new static;
        static::$currentModel->created_by_user_id = $userId;
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    protected function logoPath(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if ($value instanceof \Illuminate\Http\UploadedFile) {
                    $path = $value->store('logos', 'tenant');
                    
                    // Remove any quotes from the path
                    $path = trim($path, "\"'");
                    
                    \Log::channel('tenant')->debug('Stored logo path', [
                        'final_path' => $path
                    ]);
                    
                    return $path;
                }
                
                return $value;
            }
        );
    }

    public function usage()
    {
        return $this->hasOne(TenantUsage::class);
    }

    public function pastSubscriptions()
    {
        return $this->hasMany(Subscription::class)
            ->where('status', 'expired')
            ->orWhere('status', 'canceled')
            ->orderBy('ends_at', 'desc');
    }
}
