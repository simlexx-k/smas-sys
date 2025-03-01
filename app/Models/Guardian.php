<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\TenantBindable;

class Guardian extends Model
{
    use HasFactory, TenantBindable;

    protected $fillable = ['name', 'tenant_id'];

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
