<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Teacher extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'employee_id',
        'department',
        'qualification',
        'joining_date',
        'phone',
        'status'
    ];

    protected $casts = [
        'joining_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_teacher', 'teacher_id', 'school_class_id');
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject')
                    ->withTimestamps();
    }

    public function getRecentActivities()
    {
        return Activity::where(function ($query) {
                $query->where('user_id', $this->user_id)
                      ->orWhere(function($q) {
                          $q->where('subject_type', Teacher::class)
                            ->where('subject_id', $this->id);
                      });
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class);
    }
}
