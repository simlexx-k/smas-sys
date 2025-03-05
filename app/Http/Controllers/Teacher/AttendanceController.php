<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Models\Attendance;
use Inertia\Inertia;

class AttendanceController extends Controller
{
    public function create(SchoolClass $class)
    {
        $this->authorize('takeAttendance', $class);

        return Inertia::render('Teacher/Attendance/Create', [
            'class' => [
                'id' => $class->id,
                'name' => $class->name,
                'students' => $class->students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->user->name
                    ];
                })
            ]
        ]);
    }

    public function store(Request $request, SchoolClass $class)
    {
        $this->authorize('takeAttendance', $class);

        $validated = $request->validate([
            'date' => 'required|date',
            'attendances' => 'required|array',
            'attendances.*.student_id' => 'required|exists:students,id',
            'attendances.*.status' => 'required|in:present,absent,late'
        ]);

        foreach ($validated['attendances'] as $attendance) {
            Attendance::create([
                'class_id' => $class->id,
                'student_id' => $attendance['student_id'],
                'date' => $validated['date'],
                'status' => $attendance['status'],
                'marked_by' => auth()->id()
            ]);
        }

        return redirect()->route('teacher.classes.show', $class)
            ->with('success', 'Attendance recorded successfully');
    }
} 