<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = ['tenant_id', 'name', 'code', 'description', 'class_id'];

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class);
    }
}
