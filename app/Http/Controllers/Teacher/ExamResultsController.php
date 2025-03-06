<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Exam;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ExamResultsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tenant = $user->tenant;
        $teacher = $user->teacher;

        // Get teacher's classes
        $teacherClasses = $teacher->classes()
            ->with(['students' => function($query) {
                $query->select('students.id', 'first_name', 'last_name', 'school_class_id', 'email');
            }])
            ->get()
            ->map(function ($class) {
                return [
                    'id' => $class->id,
                    'name' => $class->name,
                    'students' => $class->students->map(function ($student) {
                        return [
                            'id' => $student->id,
                            'first_name' => $student->first_name,
                            'last_name' => $student->last_name,
                            'email' => $student->email,
                            'school_class_id' => $student->school_class_id
                        ];
                    })
                ];
            });

        // Get exams for this tenant
        $exams = Exam::where('exams.tenant_id', $tenant->id)
            ->with('term:id,name,academic_year')
            ->get()
            ->map(function ($exam) {
                return [
                    'id' => $exam->id,
                    'name' => $exam->name,
                    'start_date' => $exam->start_date,
                    'end_date' => $exam->end_date,
                    'term_name' => $exam->term->name ?? 'N/A',
                    'academic_year' => $exam->term->academic_year ?? 'N/A',
                ];
            });

        // Get subjects taught by this teacher
        $subjects = $teacher->subjects()
            ->select('subjects.id', 'subjects.name')
            ->get();

        // Custom validation rules using the correct table name
        $validator = Validator::make($request->all(), [
            'class_id' => 'nullable|exists:classes,id',
            'exam_id' => 'nullable|exists:exams,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        return Inertia::render('Teacher/ExamResults', [
            'classes' => $teacherClasses,
            'subjects' => $subjects,
            'exams' => $exams,
            'filters' => [
                'class_id' => $request->input('class_id'),
                'exam_id' => $request->input('exam_id'),
                'subject_id' => $request->input('subject_id'),
            ]
        ]);
    }

    public function getResults(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'nullable|exists:subjects,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $classId = $request->input('class_id');
            $examId = $request->input('exam_id');
            $subjectId = $request->input('subject_id');

            // Get all students in the class
            $class = SchoolClass::with(['students:id,first_name,last_name,school_class_id,email'])
                ->findOrFail($classId);

            // Get all subjects for this class if no specific subject is selected
            $subjects = $subjectId 
                ? Subject::where('id', $subjectId)->get()
                : Subject::where('class_id', $classId)
                    ->where('tenant_id', $request->user()->tenant_id)
                    ->get();

            // Base query for report cards
            $query = ReportCard::where('exam_id', $examId)
                ->whereHas('student', function($query) use ($classId) {
                    $query->where('school_class_id', $classId);
                });

            if ($subjectId) {
                $query->where('subject_id', $subjectId);
            }

            $existingResults = $query->with([
                    'student:id,first_name,last_name,school_class_id,email',
                    'subject:id,name'
                ])
                ->get()
                ->groupBy('student_id');

            // Calculate overall statistics
            $allScores = $existingResults->flatten();
            $stats = [
                'total_students' => $class->students->count(),
                'submitted_scores' => $allScores->count(),
                'class_average' => $allScores->avg('score') ?? 0,
                'highest_score' => $allScores->max('score') ?? 0,
                'lowest_score' => $allScores->min('score') ?? 0,
            ];

            // Format results for each student
            $results = $class->students->map(function ($student) use ($existingResults, $subjectId, $subjects) {
                $studentResults = $existingResults->get($student->id, collect());
                
                $baseResult = [
                    'student_id' => $student->id,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'student_id_number' => $student->email,
                ];

                if ($subjectId) {
                    // Single subject view
                    $result = $studentResults->first();
                    return array_merge($baseResult, [
                        'score' => $result ? (string)$result->score : '',
                        'grade' => $result ? $this->calculateGrade($result->score) : '',
                        'remarks' => $result ? $this->generateRemarks($result->score) : '',
                        'status' => $result ? 'submitted' : 'pending'
                    ]);
                } else {
                    // All subjects view - initialize with empty scores for all subjects
                    $subjectScores = [];
                    foreach ($subjects as $subject) {
                        $result = $studentResults->firstWhere('subject_id', $subject->id);
                        $subjectScores[] = [
                            'subject_id' => $subject->id,
                            'subject_name' => $subject->name,
                            'score' => $result ? (string)$result->score : '',
                            'grade' => $result ? $this->calculateGrade($result->score) : '',
                            'remarks' => $result ? $this->generateRemarks($result->score) : '',
                            'status' => $result ? 'submitted' : 'pending'
                        ];
                    }
                    
                    return array_merge($baseResult, [
                        'subjects' => $subjectScores
                    ]);
                }
            });

            // Calculate grade distribution
            $gradeDistribution = [
                'EE' => $allScores->filter(fn($r) => $r->score >= 76)->count(),
                'ME' => $allScores->filter(fn($r) => $r->score >= 51 && $r->score < 76)->count(),
                'AE' => $allScores->filter(fn($r) => $r->score >= 26 && $r->score < 51)->count(),
                'BE' => $allScores->filter(fn($r) => $r->score < 26)->count(),
            ];

            // Get all subjects for this class
            $availableSubjects = Subject::where('class_id', $classId)
                ->where('tenant_id', $request->user()->tenant_id)
                ->select('id', 'name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'results' => $results,
                    'statistics' => $stats,
                    'grade_distribution' => $gradeDistribution,
                    'available_subjects' => $availableSubjects,
                ],
                'message' => 'Results retrieved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve results', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve results',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'class_id' => 'required|exists:classes,id',
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'required|exists:subjects,id',
            'results' => 'required|array',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.score' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            foreach ($request->results as $result) {
                $score = floatval($result['score']);
                ReportCard::updateOrCreate(
                    [
                        'student_id' => $result['student_id'],
                        'exam_id' => $request->exam_id,
                        'subject_id' => $request->subject_id,
                    ],
                    [
                        'score' => $score,
                        'grade' => $this->calculateGrade($score),
                        'remarks' => $this->generateRemarks($score),
                        'tenant_id' => $request->user()->tenant_id,
                    ]
                );
            }

            return response()->json(['message' => 'Results saved successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to save results', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);
            return response()->json(['error' => 'Failed to save results'], 500);
        }
    }

    private function calculateGrade($score)
    {
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

    private function generateRemarks($score)
    {
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
}