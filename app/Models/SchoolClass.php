<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\TenantBindable;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Lesson;

class SchoolClass extends Model
{
    use TenantBindable;

    protected $table = 'classes';

    protected $fillable = [
        'name', 
        'grade',
        'tenant_id'
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'school_class_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(Teacher::class, 'class_teacher', 'school_class_id', 'teacher_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'class_id');
    }
}
