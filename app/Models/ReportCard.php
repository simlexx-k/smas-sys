<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'exam_id',
        'subject_id',
        'score',
        'tenant_id'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function exam()
    {
        return $this->belongsTo(Exam::class);
    }

    public function score(): HasMany
    {
        return $this->hasMany(Score::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
