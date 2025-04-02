<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TenantBindable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Exam extends Model
{
    use HasFactory, TenantBindable;

    protected $fillable = [
        'tenant_id',
        'term_id',
        'name',
        'start_date',
        'end_date',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Update status constants to match migration
    const STATUS_DRAFT = 'draft';
    const STATUS_ACTIVE = 'active';     // Changed from 'published' to 'active'
    const STATUS_COMPLETED = 'completed';

    protected $attributes = [
        'status' => self::STATUS_DRAFT
    ];

    public static function getValidStatuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_ACTIVE,      // Changed from 'published' to 'active'
            self::STATUS_COMPLETED,
        ];
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function reportCards()
    {
        return $this->hasMany(ReportCard::class);
    }

    // public function subject()
    // {
    //     return $this->belongsTo(Subject::class);
    // }

    public function getTermNameAttribute()
    {
        return $this->term ? $this->term->formatted_name : 'N/A';
    }
}
