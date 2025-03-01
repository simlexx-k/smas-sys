<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TenantBindable;

class Student extends Model
{
    use HasFactory, TenantBindable;

    protected $fillable = [
        'tenant_id',
        'school_class_id',
        'first_name',
        'last_name',
        'date_of_birth',
        'gender',
        'address',
        'phone_number',
        'email',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendance()
    {
        return $this->hasOne(Attendance::class);
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class);
    }
}
