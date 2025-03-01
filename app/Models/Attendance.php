<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant; // Add this line to import the Tenant model
use App\Models\Student; // Add this line to import the Student model

class Attendance extends Model
{
    protected $fillable = [
        'tenant_id',
        'student_id',
        'class_id',
        'date',
        'status'
    ];

    //
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
