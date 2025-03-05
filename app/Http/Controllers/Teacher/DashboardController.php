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
} 