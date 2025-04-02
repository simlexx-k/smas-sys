<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TenantBindable;

class Term extends Model
{
    use HasFactory, TenantBindable;

    protected $fillable = [
        'tenant_id',
        'name',
        'start_date',
        'end_date',
        'academic_year',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Add appends to automatically include formatted name
    protected $appends = ['formatted_name'];

    public function getFormattedNameAttribute()
    {
        return "{$this->name} ({$this->academic_year})";
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
} 