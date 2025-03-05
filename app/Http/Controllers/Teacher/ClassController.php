<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\SchoolClass;
use App\Models\Student;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $classes = $user->teacher->classes()
            ->with(['students'])
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'students_count' => $class->students->count(),
                    'subjects' => $class->subjects->pluck('name')
                ];
            });

        return Inertia::render('Teacher/Classes/Index', [
            'classes' => $classes
        ]);
    }

    public function show(SchoolClass $class)
    {
        $this->authorize('view', $class);

        $class->load(['students', 'subjects']);

        return Inertia::render('Teacher/Classes/Show', [
            'class' => [
                'id' => $class->id,
                'name' => $class->name,
                'students' => $class->students->map(function ($student) {
                    return [
                        'id' => $student->id,
                        'name' => $student->user->name,
                        'email' => $student->user->email
                    ];
                }),
                'subjects' => $class->subjects->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'name' => $subject->name
                    ];
                })
            ]
        ]);
    }
} 