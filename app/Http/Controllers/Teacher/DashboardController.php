<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Teacher;
use App\Models\SchoolClass;
use App\Models\Lesson;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;
        $teacher = $user->teacher;

        Log::info('Loading teacher dashboard', [
            'teacher_id' => $teacher->id,
            'tenant_id' => $tenant->id
        ]);

        // Get teacher's classes with student count
        $classes = $tenant->getTeacherClasses($user)
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name ?? 'Unnamed Class',
                    'grade' => $class->grade ?? 'N/A',
                    'students_count' => $class->students->count()
                ];
            });

        Log::info('Classes loaded', ['count' => $classes->count()]);

        // Get upcoming lessons with eager loaded relationships
        $upcoming_lessons = Lesson::where('tenant_id', $tenant->id)
            ->where('teacher_id', $teacher->id)
            ->where('start_time', '>=', now())
            ->with(['subject:id,name', 'class:id,name'])
            ->orderBy('start_time', 'asc')
            ->limit(5)
            ->get()
            ->map(function ($lesson) {
                return [
                    'id' => $lesson->id,
                    'subject' => [
                        'name' => $lesson->subject->name ?? 'No Subject'
                    ],
                    'class' => [
                        'name' => $lesson->class->name ?? 'No Class'
                    ],
                    'start_time' => $lesson->start_time,
                    'end_time' => $lesson->end_time,
                    'status' => $lesson->status ?? 'scheduled'
                ];
            });

        Log::info('Upcoming lessons loaded', ['count' => $upcoming_lessons->count()]);

        // Get recent activities
        $recent_activities = $teacher->getRecentActivities()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => $activity->type ?? 'general',
                    'description' => $activity->description ?? 'No description',
                    'created_at' => $activity->created_at
                ];
            });

        Log::info('Recent activities loaded', ['count' => $recent_activities->count()]);

        return Inertia::render('Dashboard', [
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name
            ],
            'teacher' => [
                'id' => $teacher->id,
                'name' => $teacher->user->name
            ],
            'classes' => $classes,
            'upcoming_lessons' => $upcoming_lessons,
            'recent_activities' => $recent_activities
        ]);
    }

    public function students(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;
        $teacher = $user->teacher;

        Log::info('Loading teacher students view', [
            'teacher_id' => $teacher->id,
            'tenant_id' => $tenant->id
        ]);

        // Get query parameters for filtering
        $search = $request->input('search');
        $classId = $request->input('class_id');
        $viewAll = $request->boolean('view_all', false);
        $sortBy = $request->input('sort_by', 'first_name');
        $sortOrder = $request->input('sort_order', 'asc');
        $perPage = $request->input('per_page', 10);

        // Get classes based on view_all parameter
        $classesQuery = $viewAll 
            ? SchoolClass::where('tenant_id', $tenant->id)
            : $teacher->classes();

        // Get classes with students
        $classes = $classesQuery
            ->with(['students' => function ($query) use ($search) {
                if ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('first_name', 'ilike', "%{$search}%")
                          ->orWhere('last_name', 'ilike', "%{$search}%")
                          ->orWhere('student_id', 'ilike', "%{$search}%");
                    });
                }
            }])
            ->get()
            ->map(function ($class) use ($sortBy, $sortOrder) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'grade' => $class->grade,
                    'students_count' => $class->students->count(),
                    'students' => $class->students->map(function ($student) {
                        return [
                            'id' => $student->id,
                            'student_id' => $student->student_id,
                            'first_name' => $student->first_name,
                            'last_name' => $student->last_name,
                            'email' => $student->email,
                            'gender' => $student->gender,
                            'date_of_birth' => $student->date_of_birth,
                            'avatar_url' => $student->avatar_url,
                            'status' => $student->status,
                            'enrollment_date' => $student->enrollment_date,
                        ];
                    })->when($sortBy, function ($collection) use ($sortBy, $sortOrder) {
                        return $collection->sortBy($sortBy, SORT_REGULAR, $sortOrder === 'desc');
                    })->values()
                ];
            });

        // If a specific class is selected, filter to show only that class
        if ($classId) {
            $classes = $classes->where('id', $classId);
        }

        Log::info('Students loaded', [
            'classes_count' => $classes->count(),
            'total_students' => $classes->sum('students_count'),
            'view_all' => $viewAll
        ]);

        return Inertia::render('Teacher/Students', [
            'classes' => $classes,
            'filters' => [
                'search' => $search,
                'class_id' => $classId,
                'view_all' => $viewAll,
                'sort_by' => $sortBy,
                'sort_order' => $sortOrder,
                'per_page' => $perPage
            ],
            'total_students' => $classes->sum('students_count')
        ]);
    }
} 