<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Exam;
use App\Models\ReportCard;
use App\Models\Subject;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;
        $teacher = $user->teacher;

        // Get teacher's classes
        $classes = $teacher->classes()
            ->with('students')
            ->get()
            ->map(fn($class) => [
                'id' => $class->id,
                'name' => $class->name,
                'students_count' => $class->students->count()
            ]);

        // Get available exams with term relationship
        $exams = Exam::where('tenant_id', $tenant->id)
            ->with('term') // Eager load the term relationship
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function($exam) {
                return [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'term' => $exam->term ? $exam->term->name : 'No Term', // Add null check
                    'start_date' => $exam->start_date,
                    'end_date' => $exam->end_date
                ];
            });

        return Inertia::render('Teacher/Reports', [
            'classes' => $classes,
            'exams' => $exams
        ]);
    }

    public function generateAcademicReport(Request $request)
    {
        try {
            $request->validate([
                'class_id' => 'required|exists:classes,id',
                'exam_id' => 'required|exists:exams,id',
                'subject_id' => 'nullable|exists:subjects,id'
            ]);

            $user = $request->user();
            $tenant = $user->tenant;
            $teacher = $user->teacher;

            // Get teacher's classes (needed for the view)
            $classes = $teacher->classes()
                ->with('students')
                ->get()
                ->map(fn($class) => [
                    'id' => $class->id,
                    'name' => $class->name,
                    'students_count' => $class->students->count()
                ]);

            // Get available exams (needed for the view)
            $exams = Exam::where('tenant_id', $tenant->id)
                ->with('term')
                ->orderBy('start_date', 'desc')
                ->get()
                ->map(function($exam) {
                    return [
                        'id' => $exam->id,
                        'name' => $exam->name,
                        'term' => $exam->term ? $exam->term->name : 'No Term',
                        'start_date' => $exam->start_date,
                        'end_date' => $exam->end_date
                    ];
                });

            $class = SchoolClass::with(['students', 'subjects'])->findOrFail($request->class_id);
            $exam = Exam::with('term')->findOrFail($request->exam_id);

            // Query report cards
            $query = ReportCard::where('exam_id', $exam->id)
                ->whereHas('student', function($q) use ($class) {
                    $q->where('school_class_id', $class->id);
                });

            if ($request->subject_id) {
                $query->where('subject_id', $request->subject_id);
            }

            $reportCards = $query->with(['student', 'subject'])->get();

            // Process student results with new grading system
            $results = $class->students->map(function($student) use ($reportCards, $class) {
                $studentReports = $reportCards->where('student_id', $student->id);
                
                // Calculate student's total and average
                $validScores = $studentReports->filter(function($report) {
                    return !is_null($report->score);
                });
                
                $totalScore = $validScores->sum('score');
                $subjectCount = $validScores->count();
                $average = $subjectCount > 0 ? $totalScore / $subjectCount : 0;

                return [
                    'student_id' => $student->id,
                    'student_name' => $student->last_name . ', ' . $student->first_name,
                    'scores' => $class->subjects->map(function($subject) use ($studentReports) {
                        $report = $studentReports->firstWhere('subject_id', $subject->id);
                        if (!$report) return null;
                        
                        return array_merge(
                            ['subject' => $subject->name],
                            $this->formatReportCardData($report)
                        );
                    }),
                    'total_score' => $totalScore,
                    'average' => round($average, 2),
                    'overall_grade' => $this->calculateGrade($average)
                ];
            });

            // Calculate class statistics with new grading system
            $stats = [
                'total_students' => $class->students->count(),
                'submitted_count' => $reportCards->count(),
                'class_average' => $reportCards->avg('score') ?? 0,
                'highest_score' => $reportCards->max('score') ?? 0,
                'lowest_score' => $reportCards->min('score') ?? 0,
                'grade_distribution' => [
                    'EE' => $reportCards->filter(fn($rc) => $rc->score >= 76)->count(),
                    'ME' => $reportCards->filter(fn($rc) => $rc->score >= 51 && $rc->score < 76)->count(),
                    'AE' => $reportCards->filter(fn($rc) => $rc->score >= 26 && $rc->score < 51)->count(),
                    'BE' => $reportCards->filter(fn($rc) => $rc->score < 26)->count(),
                ]
            ];

            // Sort students by average score for ranking
            $results = $results->sortByDesc('average')->values();
            
            // Add ranking to results
            $results = $results->map(function($result, $index) {
                $result['rank'] = $index + 1;
                $result['rank_suffix'] = $this->getOrdinalSuffix($index + 1);
                return $result;
            });

            return Inertia::render('Teacher/Reports', [
                'classes' => $classes,
                'exams' => $exams,
                'reportData' => [
                    'class' => [
                        'name' => $class->name,
                        'grade' => $class->grade
                    ],
                    'exam' => [
                        'name' => $exam->name,
                        'term' => $exam->term ? $exam->term->name : 'No Term'
                    ],
                    'statistics' => $stats,
                    'results' => $results
                ],
                'success' => true
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to generate academic report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Get the data again for error state
            $user = $request->user();
            $tenant = $user->tenant;
            $teacher = $user->teacher;

            $classes = $teacher->classes()
                ->with('students')
                ->get()
                ->map(fn($class) => [
                    'id' => $class->id,
                    'name' => $class->name,
                    'students_count' => $class->students->count()
                ]);

            $exams = Exam::where('tenant_id', $tenant->id)
                ->with('term')
                ->orderBy('start_date', 'desc')
                ->get()
                ->map(function($exam) {
                    return [
                        'id' => $exam->id,
                        'name' => $exam->name,
                        'term' => $exam->term ? $exam->term->name : 'No Term',
                        'start_date' => $exam->start_date,
                        'end_date' => $exam->end_date
                    ];
                });

            return Inertia::render('Teacher/Reports', [
                'classes' => $classes,
                'exams' => $exams,
                'error' => 'Failed to generate academic report',
                'success' => false
            ]);
        }
    }

    public function generateAttendanceReport(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:classes,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        try {
            $class = SchoolClass::with('students')->findOrFail($request->class_id);
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            // Get attendance records
            $attendanceRecords = Attendance::where('class_id', $class->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->get()
                ->groupBy('student_id');

            // Calculate total school days
            $totalDays = $endDate->diffInDays($startDate) + 1;

            // Generate report for each student
            $results = $class->students->map(function($student) use ($attendanceRecords, $totalDays) {
                $records = $attendanceRecords->get($student->id, collect());
                
                return [
                    'student_id' => $student->id,
                    'student_name' => $student->last_name . ', ' . $student->first_name,
                    'total_days' => $totalDays,
                    'present_days' => $records->where('status', 'present')->count(),
                    'absent_days' => $records->where('status', 'absent')->count(),
                    'late_days' => $records->where('status', 'late')->count(),
                    'attendance_rate' => round(($records->where('status', 'present')->count() / $totalDays) * 100, 2)
                ];
            });

            // Calculate class statistics
            $stats = [
                'average_attendance_rate' => $results->avg('attendance_rate'),
                'total_students' => $class->students->count(),
                'perfect_attendance' => $results->where('attendance_rate', 100)->count()
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'class' => [
                        'name' => $class->name,
                        'grade' => $class->grade
                    ],
                    'period' => [
                        'start' => $startDate->format('Y-m-d'),
                        'end' => $endDate->format('Y-m-d'),
                        'total_days' => $totalDays
                    ],
                    'statistics' => $stats,
                    'results' => $results
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate attendance report', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate attendance report'
            ], 500);
        }
    }

    public function downloadPDF(Request $request)
    {
        try {
            $request->validate([
                'class_id' => 'required|exists:classes,id',
                'exam_id' => 'required|exists:exams,id',
                'report_type' => 'required|in:academic,attendance'
            ]);

            if ($request->report_type === 'academic') {
                return $this->downloadAcademicPDF($request);
            } else {
                return $this->downloadAttendancePDF($request);
            }

        } catch (\Exception $e) {
            Log::error('Failed to generate PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // For GET requests, redirect back with error
            return redirect()->back()->with('error', 'Failed to generate PDF');
        }
    }

    private function downloadAcademicPDF(Request $request)
    {
        $tenant = $request->user()->tenant;
        $class = SchoolClass::with(['students', 'subjects'])->findOrFail($request->class_id);
        $exam = Exam::with('term')->findOrFail($request->exam_id);

        // Query report cards
        $reportCards = ReportCard::where('exam_id', $exam->id)
            ->whereHas('student', function($q) use ($class) {
                $q->where('school_class_id', $class->id);
            })
            ->with(['student', 'subject'])
            ->get();

        // Format student results
        $results = $class->students->map(function($student) use ($reportCards, $class) {
            $studentReports = $reportCards->where('student_id', $student->id);
            
            // Calculate student's total and average
            $validScores = $studentReports->filter(function($report) {
                return !is_null($report->score);
            });
            
            $totalScore = $validScores->sum('score');
            $subjectCount = $validScores->count();
            $average = $subjectCount > 0 ? $totalScore / $subjectCount : 0;

            return [
                'student_id' => $student->id,
                'student_name' => $student->last_name . ', ' . $student->first_name,
                'scores' => $class->subjects->map(function($subject) use ($studentReports) {
                    $report = $studentReports->firstWhere('subject_id', $subject->id);
                    $score = $report ? $report->score : null;
                    
                    return [
                        'subject' => $subject->name,
                        'score' => $score,
                        'grade' => $score ? $this->calculateGrade($score) : null,
                        'remarks' => $score ? $this->generateRemarks($score) : null
                    ];
                }),
                'total_score' => $totalScore,
                'average' => round($average, 2)
            ];
        });

        // Sort students by average and add ranking
        $results = $results->sortByDesc('average')
            ->values()
            ->map(function($result, $index) {
                return array_merge($result, [
                    'rank' => $index + 1,
                    'rank_suffix' => $this->getOrdinalSuffix($index + 1),
                    'overall_grade' => $this->calculateGrade($result['average'])
                ]);
            });

        // Calculate statistics
        $stats = [
            'total_students' => $class->students->count(),
            'submitted_count' => $reportCards->count(),
            'class_average' => $reportCards->avg('score') ?? 0,
            'highest_score' => $reportCards->max('score') ?? 0,
            'lowest_score' => $reportCards->min('score') ?? 0,
            'grade_distribution' => [
                'EE' => $reportCards->filter(fn($rc) => $rc->score >= 76)->count(),
                'ME' => $reportCards->filter(fn($rc) => $rc->score >= 51 && $rc->score < 76)->count(),
                'AE' => $reportCards->filter(fn($rc) => $rc->score >= 26 && $rc->score < 51)->count(),
                'BE' => $reportCards->filter(fn($rc) => $rc->score < 26)->count(),
            ]
        ];

        $data = [
            'tenant' => $tenant,
            'class' => [
                'name' => $class->name,
                'grade' => $class->grade
            ],
            'exam' => [
                'name' => $exam->name,
                'term' => $exam->term ? $exam->term->name : 'No Term'
            ],
            'statistics' => $stats,
            'results' => $results,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ];

        $pdf = PDF::loadView('pdfs.academic-report', $data);
        
        return $pdf->download("academic-report-{$class->name}-{$exam->name}.pdf");
    }

    private function calculateGrade($score) {
        switch (true) {
            case $score >= 76:
                return 'EE'; // Exceeding Expectation
            case $score >= 51:
                return 'ME'; // Meeting Expectation
            case $score >= 26:
                return 'AE'; // Approaching Expectation
            default:
                return 'BE'; // Below Expectation
        }
    }

    private function generateRemarks($score) {
        switch (true) {
            case $score >= 76:
                return "Exceeding Expectation. Shows exceptional understanding and mastery of subject matter.";
            case $score >= 51:
                return "Meeting Expectation. Demonstrates good understanding of core concepts.";
            case $score >= 26:
                return "Approaching Expectation. Shows basic understanding but needs more practice.";
            default:
                return "Below Expectation. Requires immediate intervention and support.";
        }
    }

    private function getOrdinalSuffix($number) {
        if (!in_array(($number % 100), [11, 12, 13])) {
            switch ($number % 10) {
                case 1:  return 'st';
                case 2:  return 'nd';
                case 3:  return 'rd';
            }
        }
        return 'th';
    }

    private function formatReportCardData($reportCard)
    {
        $score = $reportCard->score;
        return [
            'score' => $score,
            'grade' => $this->calculateGrade($score),
            'remarks' => $this->generateRemarks($score)
        ];
    }
} 