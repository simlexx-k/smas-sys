<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'action',
        'description',
        'subject_type',
        'subject_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    protected $with = ['user']; // Always load the user relationship

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper method to log activity
    public static function log($type, $action, $description, $subject = null)
    {
        return static::create([
            'user_id' => auth()->id(),
            'type' => $type,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject ? $subject->id : null,
        ]);
    }
} 