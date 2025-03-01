<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\TenantBindable;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\Teacher;

class SchoolClass extends Model
{
    use TenantBindable;

    protected $table = 'classes';

    protected $fillable = ['name', 'tenant_id'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }
}
