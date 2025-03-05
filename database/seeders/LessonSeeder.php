<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Lesson;
use App\Models\Teacher;
use App\Models\Subject;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LessonSeeder extends Seeder
{
    public function run()
    {
        Log::info('Starting LessonSeeder...');

        // First, let's ensure we have some subjects
        $this->createSubjectsIfNeeded();

        // Get all teachers with their relationships
        $teachers = Teacher::with(['classes', 'tenant'])->get();
        Log::info('Found teachers:', ['count' => $teachers->count()]);

        $teachers->each(function ($teacher) {
            Log::info('Processing teacher:', [
                'teacher_id' => $teacher->id,
                'tenant_id' => $teacher->tenant_id,
                'classes_count' => $teacher->classes->count()
            ]);

            // Skip if teacher has no classes
            if ($teacher->classes->isEmpty()) {
                Log::warning('Teacher has no classes, skipping...', ['teacher_id' => $teacher->id]);
                return;
            }

            // Get subjects for this tenant
            $subjects = Subject::where('tenant_id', $teacher->tenant_id)->get();
            Log::info('Found subjects for tenant:', [
                'tenant_id' => $teacher->tenant_id,
                'subjects_count' => $subjects->count()
            ]);
            
            // Skip if no subjects
            if ($subjects->isEmpty()) {
                Log::warning('No subjects found for tenant, skipping...', ['tenant_id' => $teacher->tenant_id]);
                return;
            }

            // For each teacher's class
            $teacher->classes->each(function ($class) use ($teacher, $subjects) {
                Log::info('Processing class:', [
                    'class_id' => $class->id,
                    'teacher_id' => $teacher->id
                ]);

                // Create lessons for the next 7 days
                for ($i = 0; $i < 7; $i++) {
                    $date = Carbon::now()->addDays($i);
                    
                    // Create 2-3 lessons per day
                    $lessonsPerDay = rand(2, 3);
                    Log::info('Creating lessons for date:', [
                        'date' => $date->toDateString(),
                        'lessons_count' => $lessonsPerDay
                    ]);

                    for ($j = 0; $j < $lessonsPerDay; $j++) {
                        $startTime = $date->copy()->setHour(8 + $j * 2)->setMinute(0);
                        
                        try {
                            $lesson = Lesson::create([
                                'subject_id' => $subjects->random()->id,
                                'class_id' => $class->id,
                                'teacher_id' => $teacher->id,
                                'tenant_id' => $teacher->tenant_id,
                                'start_time' => $startTime,
                                'end_time' => $startTime->copy()->addHours(1),
                                'status' => 'scheduled'
                            ]);

                            Log::info('Created lesson:', [
                                'lesson_id' => $lesson->id,
                                'start_time' => $startTime->toDateTimeString()
                            ]);
                        } catch (\Exception $e) {
                            Log::error('Failed to create lesson:', [
                                'error' => $e->getMessage(),
                                'class_id' => $class->id,
                                'teacher_id' => $teacher->id
                            ]);
                        }
                    }
                }
            });
        });

        Log::info('LessonSeeder completed.');
    }

    private function createSubjectsIfNeeded()
    {
        Log::info('Starting subject creation...');

        $defaultSubjects = [
            ['name' => 'Mathematics', 'code' => 'MAT001'],
            ['name' => 'English', 'code' => 'ENG001'],
            ['name' => 'Science', 'code' => 'SCI001'],
            ['name' => 'Social Studies', 'code' => 'SOC001'],
            ['name' => 'Physical Education', 'code' => 'PHY001'],
            ['name' => 'Art', 'code' => 'ART001'],
            ['name' => 'Music', 'code' => 'MUS001']
        ];

        // Get all tenant IDs
        $tenantIds = Teacher::distinct('tenant_id')->pluck('tenant_id');
        Log::info('Found tenants:', ['count' => $tenantIds->count()]);

        foreach ($tenantIds as $tenantId) {
            Log::info('Creating subjects for tenant:', ['tenant_id' => $tenantId]);

            foreach ($defaultSubjects as $subject) {
                try {
                    $created = Subject::firstOrCreate(
                        [
                            'name' => $subject['name'],
                            'code' => $subject['code'],
                            'tenant_id' => $tenantId
                        ],
                        [
                            'description' => "Standard {$subject['name']} curriculum"
                        ]
                    );

                    Log::info('Subject processed:', [
                        'name' => $subject['name'],
                        'tenant_id' => $tenantId,
                        'was_created' => $created->wasRecentlyCreated
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to create subject:', [
                        'name' => $subject['name'],
                        'tenant_id' => $tenantId,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        Log::info('Subject creation completed.');
    }
} 