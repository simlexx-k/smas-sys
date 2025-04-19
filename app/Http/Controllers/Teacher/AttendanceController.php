<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Models\Attendance;
use Inertia\Inertia;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AttendanceController extends Controller
{
    use AuthorizesRequests;

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

    public function index(Request $request)
    {
        \Log::info('Fetching attendances for tenant:', ['tenant_id' => auth()->user()->tenant_id]);
        
        // For summary view
        if ($request->has('summary')) {
            $data = Attendance::where('tenant_id', auth()->user()->tenant_id)
                ->selectRaw("date, 
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present,
                    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent,
                    SUM(CASE WHEN status = 'late' THEN 1 ELSE 0 END) as late")
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get();

            \Log::debug('Attendance summary API response:', $data->toArray());
            return response()->json($data);
        }

        // For detailed view
        $data = Attendance::where('tenant_id', auth()->user()->tenant_id)
            ->with(['student', 'class'])
            ->orderBy('date', 'desc')
            ->get();

        \Log::debug('Detailed attendances API response:', $data->toArray());
        return response()->json($data);
    }
} 