<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    protected $fillable = ['tenant_id', 'name', 'date'];

    // public function subject()
    // {
    //     return $this->belongsTo(Subject::class);
    // }
}
