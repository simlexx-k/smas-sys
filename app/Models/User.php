<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Tenant;

class User extends Authenticatable
{
    const ROLE_LANDLORD = 'landlord';
    const ROLE_TENANT_ADMIN = 'tenant-admin';
    const ROLE_TEACHER = 'teacher';
    const ROLE_STUDENT = 'student';

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'tenant_id',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    public function isTeacher(): bool
    {
        return $this->role === self::ROLE_TEACHER;
    }

    public function getAvailableRoles(): array
    {
        if ($this->role === self::ROLE_LANDLORD) {
            return [self::ROLE_LANDLORD];
        }

        return [
            self::ROLE_TENANT_ADMIN,
            self::ROLE_TEACHER,
            self::ROLE_STUDENT
        ];
    }

    public function hasAnyRoleCustom(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    /**
     * Boot function from Laravel.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (!$user->role) {
                $user->role = self::ROLE_LANDLORD;
            }
        });
    }
}
