<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TenantBindable;

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

    public function term()
    {
        return $this->belongsTo(Term::class);
    }

    public function tenant()
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
}
