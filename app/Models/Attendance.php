<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tenant; // Add this line to import the Tenant model
use App\Models\Student; // Add this line to import the Student model
use App\Models\User; // Add this line to import the User model
use App\Models\SchoolClass; // Add this line to import the SchoolClass model

class Attendance extends Model
{
    protected $fillable = [
        'tenant_id',
        'student_id',
        'class_id',
        'date',
        'status',
        'marked_by'
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

    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
}
