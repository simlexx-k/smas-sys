<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    protected $fillable = ['name', 'email', 'tenant_id'];

    public function classes(): HasMany
    {
        return $this->hasMany(SchoolClass::class);
    }
}
