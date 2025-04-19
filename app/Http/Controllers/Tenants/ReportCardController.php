<?php

namespace App\Http\Controllers\Tenants;

use App\Http\Controllers\Controller;
use App\Models\ReportCard;
use App\Models\Student;
use App\Models\Exam;
use App\Models\Subject;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\ReportCardDataSanitizer;
use App\Models\SchoolClass;
use ZipArchive;
use Illuminate\Support\Facades\Storage;
use Jurosh\PDFMerge\PDFMerger;

class ReportCardController extends Controller
{
    private $sanitizer;

    public function __construct(ReportCardDataSanitizer $sanitizer)
    {
        $this->sanitizer = $sanitizer;
    }

    public function index(Request $request)
    {
        $query = ReportCard::with([
            'student' => function($query) {
                $query->select('id', 'first_name', 'last_name', 'school_class_id');
            },
            'exam' => function($query) {
                $query->select('id', 'name');
            },
            'subject' => function($query) {
                $query->select('id', 'name');
            }
        ])->where('tenant_id', $request->tenant_id);

        // Apply filters
        if ($request->has('student_id') && $request->student_id) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->has('exam_id') && $request->exam_id) {
            $query->where('exam_id', $request->exam_id);
        }
        if ($request->has('subject_id') && $request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->has('class_id') && $request->class_id) {
            $query->whereHas('student', function($query) use ($request) {
                $query->where('school_class_id', $request->class_id);
            });
        }

        $reportCards = $query->get();
        
        return response()->json($reportCards);
    }

    public function create()
    {
        // Removed view return statement
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'exam_id' => 'required|exists:exams,id',
            'subject_id' => 'required|exists:subjects,id',
            'score' => 'required|numeric|between:0,100',
            'remarks' => 'nullable|string|max:255',
            'grade' => 'required|string|max:2',
            'tenant_id' => 'required|exists:tenants,id',
        ]);

        $reportCard = ReportCard::create($validated);

        return response()->json($reportCard, 201);
    }

    public function show(ReportCard $reportCard)
    {
        return response()->json($reportCard);
    }

    public function edit(ReportCard $reportCard)
    {
        // Removed view return statement
    }

    public function update(Request $request, ReportCard $reportCard)
    {
        $validated = $request->validate([
            'student_id' => 'sometimes|exists:students,id',
            'exam_id' => 'sometimes|exists:exams,id',
            'subject_id' => 'sometimes|exists:subjects,id',
            'score' => 'sometimes|numeric|between:0,100',
            'remarks' => 'nullable|string|max:255',
            'grade' => 'sometimes|string|max:2',
            'tenant_id' => 'sometimes|exists:tenants,id'
        ]);

        $reportCard->update($validated);

        return response()->json($reportCard);
    }

    public function destroy(ReportCard $reportCard)
    {
        $reportCard->delete();
        return response()->noContent();
    }

    public function batchPrint(Request $request)
    {
        Log::info('Batch print request received', $request->all());

        $request->validate([
            'class_id' => 'required|integer|exists:classes,id',
            'exam_id' => 'required|integer|exists:exams,id',
            'type' => 'required|in:individual,nominal,batch',
            'tenant_id' => 'required|exists:tenants,id',
            'student_id' => 'required_if:type,individual|exists:students,id'
        ]);

        // Add tenant_id condition to all queries
        $classId = $request->query('class_id');
        $examId = $request->query('exam_id');
        $type = $request->query('type');
        $tenantId = $request->query('tenant_id');

        // Log the class details we're trying to fetch
        $class = SchoolClass::where('id', $classId)
            ->where('tenant_id', $tenantId)
            ->first();
        
        Log::info('Class details:', [
            'class_id' => $classId,
            'tenant_id' => $tenantId,
            'class' => $class ? $class->toArray() : 'Not found'
        ]);

        if (!$class) {
            Log::error('Class not found', [
                'class_id' => $classId,
                'tenant_id' => $tenantId
            ]);
            return response()->json(['error' => 'Class not found'], 404);
        }

        if ($type === 'individual') {
            $studentId = $request->query('student_id');
            Log::info('Generating individual PDF', ['student_id' => $studentId]);

            // Get report card for specific student
            $reportCard = ReportCard::where('student_id', $studentId)
                ->where('exam_id', $examId)
                ->with(['student.schoolClass', 'exam'])
                ->first();

            if (!$reportCard) {
                Log::warning('No report card found', [
                    'student_id' => $studentId,
                    'exam_id' => $examId
                ]);
                return response()->json(['error' => 'No report card found for this student'], 404);
            }

            try {
                $pdf = $this->generatePdf($reportCard);
                $fileName = "report-card-{$reportCard->student->full_name}.pdf";
                
                return response()->streamDownload(function() use ($pdf) {
                    echo $pdf;
                }, $fileName);
            } catch (\Exception $e) {
                Log::error('Failed to generate PDF for student', [
                    'student_id' => $studentId,
                    'error' => $e->getMessage()
                ]);
                return response()->json(['error' => 'Failed to generate PDF'], 500);
            }
        }

        Log::info('Fetching students for class', ['class_id' => $classId]);
        $students = Student::where('school_class_id', $classId)->get();

        if ($students->isEmpty()) {
            Log::warning('No students found for class', ['class_id' => $classId]);
            return response()->json(['error' => 'No students found for this class'], 404);
        }

        Log::info('Fetching report cards for students', [
            'student_ids' => $students->pluck('id'),
            'exam_id' => $examId
        ]);

        // Get one report card per student (we'll fetch all subjects in generatePdf)
        $reportCards = ReportCard::whereIn('student_id', $students->pluck('id'))
            ->where('exam_id', $examId)
            ->select('student_id') // First get unique student IDs
            ->groupBy('student_id')
            ->with(['student.schoolClass', 'exam'])
            ->get()
            ->map(function($reportCard) use ($examId) {
                // Then get one report card for each student
                return ReportCard::where('student_id', $reportCard->student_id)
                    ->where('exam_id', $examId)
                    ->with(['student.schoolClass', 'exam'])
                    ->first();
            });

        // Remove any null values that might have occurred
        $reportCards = $reportCards->filter();

        Log::info('Found report cards:', [
            'count' => $reportCards->count(),
            'first_report_card' => $reportCards->first() ? [
                'student_id' => $reportCards->first()->student_id,
                'exam_id' => $reportCards->first()->exam_id,
                'student_name' => $reportCards->first()->student->full_name ?? 'N/A'
            ] : null
        ]);

        if ($reportCards->isEmpty()) {
            Log::warning('No report cards found', [
                'class_id' => $classId,
                'exam_id' => $examId,
                'student_ids' => $students->pluck('id')
            ]);
            return response()->json(['error' => 'No report cards found for these students'], 404);
        }

        if ($type === 'nominal') {
            Log::info('Generating nominal list PDF');
            return response()->streamDownload(function() use ($reportCards) {
                echo $this->generateNominalListPdf($reportCards);
            }, 'nominal-list.pdf');
        } elseif ($type === 'batch') {
            Log::info('Generating batch report cards PDF');
            try {
                return $this->generateBatchReportCards($classId, $examId);
            } catch (\Exception $e) {
                Log::error('Failed to generate batch report cards', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json(['error' => 'Failed to generate batch report cards'], 500);
            }
        }

        Log::error('Invalid type specified', ['type' => $type]);
        return response()->json(['error' => 'Invalid type'], 400);
    }

    private function validateReportCardData($reportCard)
    {
        if (!$reportCard->student || !$reportCard->exam || !$reportCard->subject) {
            Log::error('Missing required report card data', [
                'student' => $reportCard->student,
                'exam' => $reportCard->exam,
                'subject' => $reportCard->subject
            ]);
            throw new \InvalidArgumentException('Missing required report card data');
        }
    }

    private function generatePdf($reportCard)
    {
        Log::info('Starting report card generation...');

        // Get all subject scores for this student and exam
        $allSubjectScores = ReportCard::where('student_id', $reportCard->student_id)
            ->where('exam_id', $reportCard->exam_id)
            ->with(['subject', 'student.schoolClass', 'exam', 'tenant'])
            ->get();

        Log::info('All subject scores:', [
            'student_id' => $reportCard->student_id,
            'exam_id' => $reportCard->exam_id,
            'scores_count' => $allSubjectScores->count(),
            'scores' => ['Illuminate\Support\Collection' => $allSubjectScores->map(function($score) {
                return [
                    'subject' => $score->subject->name,
                    'score' => number_format($score->score, 2),
                    'grade' => $score->grade
                ];
            })]
        ]);

        // Process subjects with auto-generated remarks
        $subjects = $allSubjectScores->map(function($score) {
            $scoreValue = floatval($score->score);
            return [
                'name' => $score->subject->name,
                'score' => number_format($scoreValue, 2),
                'grade' => $this->calculateGrade($scoreValue),
                'remarks' => $this->generateRemarks($scoreValue) // Use auto-generated remarks
            ];
        })->toArray();

        // Calculate summary
        $total_score = $allSubjectScores->sum('score');
        $total_subjects = $allSubjectScores->count();
        $average_score = $total_subjects > 0 ? round($total_score / $total_subjects, 2) : 0;

        // Get student details
        $student_details = [
            'name' => $reportCard->student->full_name ?? $reportCard->student->first_name . ' ' . $reportCard->student->last_name,
            'admission_number' => $reportCard->student->admission_number ?? 'N/A',
            'class' => $reportCard->student->schoolClass->name ?? 'N/A',
            'school_name' => $reportCard->tenant->name ?? 'N/A',
            'school_address' => $reportCard->tenant->address ?? '',
            'school_phone' => $reportCard->tenant->phone ?? '',
            'school_email' => $reportCard->tenant->email ?? '',
            'school_logo' => $reportCard->tenant->logo_url ?? null
        ];

        // Get exam details
        $exam_details = [
            'name' => $reportCard->exam->name ?? 'N/A',
            'term' => $reportCard->exam->term_name,
            'year' => $reportCard->exam->academic_year
        ];

        // Calculate rank
        $class_rankings = ReportCard::where('exam_id', $reportCard->exam_id)
            ->whereHas('student', function($query) use ($reportCard) {
                $query->where('school_class_id', $reportCard->student->school_class_id);
            })
            ->select('student_id', DB::raw('AVG(score) as average'))
            ->groupBy('student_id')
            ->orderByDesc('average')
            ->get();

        $student_rank = $class_rankings->search(function($item) use ($reportCard) {
            return $item->student_id === $reportCard->student_id;
        }) + 1;

        $summary = [
            'total_score' => $total_score,
            'total_subjects' => $total_subjects,
            'average_score' => $average_score,
            'overall_grade' => $this->calculateGrade($average_score),
            'rank' => $student_rank . ' out of ' . $class_rankings->count(),
            'percentage' => $average_score
        ];

        // Generate PDF
        return PDF::loadView('pdf.report_card', compact(
            'student_details',
            'exam_details',
            'subjects',
            'summary'
        ))->output();
    }

    private function generateNominalListPdf($reportCards)
    {
        try {
            if (empty($reportCards)) {
                throw new \InvalidArgumentException('No report cards provided');
            }

            // Get the first report card to access common data
            $firstCard = $reportCards->first();
            
            // Get all report cards for this exam and class
            $allReportCards = ReportCard::where('exam_id', $firstCard->exam_id)
                ->whereHas('student', function($query) use ($firstCard) {
                    $query->where('school_class_id', $firstCard->student->school_class_id);
                })
                ->with(['student', 'subject', 'exam'])
                ->get();

            // Get all subjects for this exam
            $subjects = Subject::whereIn('id', $allReportCards->pluck('subject_id')->unique())
                ->orderBy('name')
                ->get();

            // Get all students in the class
            $students = Student::where('school_class_id', $firstCard->student->school_class_id)
                ->orderBy('first_name')
                ->get();

            // Organize scores by student and subject
            $scores = [];
            foreach ($allReportCards as $rc) {
                $scores[$rc->student_id][$rc->subject_id] = [
                    'score' => $rc->score,
                    'grade' => $rc->grade
                ];
            }

            // Calculate averages and prepare for sorting
            $studentAverages = [];
            foreach ($students as $student) {
                $studentScores = $scores[$student->id] ?? [];
                $validScores = array_filter($studentScores, 'is_numeric');
                $average = !empty($validScores) ? array_sum($validScores) / count($validScores) : 0;
                $studentAverages[$student->id] = round($average, 1); // Round to 1 decimal place
            }

            // Sort students by average score (descending)
            arsort($studentAverages);

            // Calculate positions (handling ties)
            $position = 0;
            $lastScore = null;
            $positions = [];
            foreach ($studentAverages as $studentId => $average) {
                if ($average !== $lastScore) {
                    $position = count($positions) + 1;
                }
                $positions[$studentId] = $position;
                $lastScore = $average;
            }

            // Prepare results array
            $results = [];
            foreach ($students as $student) {
                $studentScores = [];
                foreach ($subjects as $subject) {
                    $score = $scores[$student->id][$subject->id] ?? null;
                    $scoreValue = is_numeric($score) ? floatval($score) : null;
                    $studentScores[] = [
                        'subject' => $subject->name,
                        'score' => $scoreValue,
                        'grade' => $scoreValue ? $this->calculateGrade($scoreValue) : null
                    ];
                }

                $validScores = collect($studentScores)->filter(fn($score) => !is_null($score['score']));
                $total = $validScores->sum('score');
                $average = $validScores->count() > 0 ? round($total / $validScores->count(), 1) : 0;
                $position = $positions[$student->id] ?? 0;

                $results[] = [
                    'student_name' => $student->full_name,
                    'rank' => $this->getOrdinalSuffix($position),
                    'scores' => $studentScores,
                    'total_score' => round($total, 1),
                    'average' => $average,
                    'overall_grade' => $this->calculateGrade($average)
                ];
            }

            // Sort results by rank and then by average (descending) for ties
            usort($results, function($a, $b) {
                if ($a['rank'] === $b['rank']) {
                    return $b['average'] <=> $a['average'];
                }
                return $a['rank'] <=> $b['rank'];
            });

            // Calculate statistics
            $statistics = [
                'class_average' => collect($results)->avg('average'),
                'highest_score' => collect($results)->max('average'),
                'lowest_score' => collect($results)->min('average'),
                'total_students' => count($results)
            ];

            // Prepare data for the template
            $data = [
                'tenant' => $firstCard->tenant,
                'class' => ['name' => $firstCard->student->schoolClass->name],
                'exam' => [
                    'name' => $firstCard->exam->name,
                    'term' => $firstCard->exam->term_name ?? 'N/A'
                ],
                'results' => $results,
                'statistics' => $statistics,
                'generated_at' => now()->format('Y-m-d H:i')
            ];

            return PDF::loadView('pdfs.academic-report', $data)->output();

        } catch (\Exception $e) {
            Log::error('PDF generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \RuntimeException('Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function bulkStore(Request $request)
    {
        try {
            $validatedData = $request->validate([
                '*.student_id' => 'required|exists:students,id',
                '*.exam_id' => 'required|exists:exams,id',
                '*.subject_id' => 'required|exists:subjects,id',
                '*.tenant_id' => 'required|exists:tenants,id',
                '*.score' => 'required|numeric|min:0|max:100',
            ]);

            DB::beginTransaction();

            foreach ($validatedData as $data) {
                ReportCard::updateOrCreate(
                    [
                        'student_id' => $data['student_id'],
                        'exam_id' => $data['exam_id'],
                        'subject_id' => $data['subject_id'],
                        'tenant_id' => $data['tenant_id'],
                    ],
                    [
                        'score' => $data['score'],
                    ]
                );
            }

            DB::commit();

            return response()->json(['message' => 'Report cards saved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to save report cards:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to save report cards',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function generateBatchReportCards($classId, $examId)
    {
        try {
            Log::info('Starting batch report cards generation', [
                'class_id' => $classId,
                'exam_id' => $examId
            ]);

            // Create temp directory if it doesn't exist
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0777, true);
            }

            // Get all students in the class
            $students = Student::where('school_class_id', $classId)
                ->orderBy('first_name')
                ->get();

            if ($students->isEmpty()) {
                Log::warning('No students found in class', ['class_id' => $classId]);
                throw new \InvalidArgumentException('No students found in this class');
            }

            // Get all report cards for this exam and class
            $allReportCards = ReportCard::where('exam_id', $examId)
                ->whereIn('student_id', $students->pluck('id'))
                ->with(['student', 'subject', 'exam.term', 'tenant']) // Make sure to eager load term
                ->get()
                ->groupBy('student_id');

            Log::info('Found report cards', [
                'total_students' => $students->count(),
                'students_with_reports' => $allReportCards->count()
            ]);

            // Calculate averages and ranks
            $studentAverages = [];
            foreach ($students as $student) {
                $studentReportCards = $allReportCards->get($student->id);
                if ($studentReportCards && $studentReportCards->count() > 0) {
                    $validScores = $studentReportCards->filter(function($rc) {
                        return !is_null($rc->score);
                    });
                    
                    $total = $validScores->sum('score');
                    $count = $validScores->count();
                    $average = $count > 0 ? round($total / $count, 2) : 0;
                    
                    $studentAverages[$student->id] = [
                        'average' => $average,
                        'student' => $student
                    ];
                }
            }

            // Sort by average in descending order
            uasort($studentAverages, function($a, $b) {
                return $b['average'] <=> $a['average'];
            });

            // Assign ranks
            $rank = 1;
            $totalStudents = count($studentAverages);
            foreach ($studentAverages as &$data) {
                $data['rank'] = $rank;
                $data['total_students'] = $totalStudents;
                $rank++;
            }

            $tempFiles = [];
            // Get the class and exam details
            $class = SchoolClass::findOrFail($classId);
            $exam = Exam::with('term')->findOrFail($examId);

            // Calculate class statistics
            $classStats = [
                'total_score' => 0,
                'student_count' => 0,
                'highest_score' => null, // Initialize as null
                'lowest_score' => 100 // Start with maximum possible score
            ];

            // First pass to gather statistics
            foreach ($students as $student) {
                $studentReportCards = $allReportCards->get($student->id);
                if ($studentReportCards && $studentReportCards->count() > 0) {
                    $reportCard = $studentReportCards->first();
                    if (isset($reportCard->average)) {
                        $score = $reportCard->average;
                        $classStats['total_score'] += $score;
                        $classStats['student_count']++;
                        // Update highest and lowest scores
                        if ($classStats['highest_score'] === null || $score > $classStats['highest_score']) {
                            $classStats['highest_score'] = $score;
                        }
                        if ($score < $classStats['lowest_score']) {
                            $classStats['lowest_score'] = $score;
                        }
                    }
                }
            }

            // Calculate class average
            $classStats['class_average'] = $classStats['student_count'] > 0 
                ? round($classStats['total_score'] / $classStats['student_count'], 2) 
                : 0;

            // Reset lowest score if no students found
            if ($classStats['student_count'] === 0) {
                $classStats['lowest_score'] = 0;
            }

            foreach ($students as $student) {
                try {
                    $studentReportCards = $allReportCards->get($student->id);
                    if ($studentReportCards && $studentReportCards->count() > 0) {
                        $tenant = auth()->user()->tenant ?? $studentReportCards->first()->tenant;
                        
                        // Generate rank data
                        $rankData = isset($studentAverages[$student->id]) ? [
                            'rank' => $studentAverages[$student->id]['rank'],
                            'total_students' => count($studentAverages),
                            'class_average' => $classStats['class_average'],
                            'highest_score' => $classStats['highest_score'] // Ensure this is set
                        ] : null;

                        // Create PDF instance first
                        $pdf = PDF::setPaper('A4', 'landscape');
                        
                        // Then load the view with data
                        $pdf->loadView('pdfs.academic-report', [
                            'report_card' => $studentReportCards->first(),
                            'result' => $rankData,
                            'statistics' => [
                                'total_students' => count($studentAverages),
                                'class_average' => $classStats['class_average'],
                                'highest_score' => $classStats['highest_score'], // Ensure this is set
                                'lowest_score' => $classStats['lowest_score']
                            ],
                            'generated_at' => now()->format('Y-m-d H:i:s'),
                            'tenant' => $tenant,
                            'class' => $class,
                            'exam' => $exam
                        ]);

                        // Save to temporary file with unique name
                        $tempFile = storage_path('app/temp/' . uniqid('report_') . '.pdf');
                        $pdf->save($tempFile);
                        $tempFiles[] = $tempFile;
                    }
                } catch (\Exception $e) {
                    \Log::error("Failed to generate PDF for student {$student->id}: " . $e->getMessage());
                    // Clean up temp files
                    foreach ($tempFiles as $file) {
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                    throw $e;
                }
            }

            // Merge PDFs if we have any
            if (count($tempFiles) > 0) {
                $merger = new \Jurosh\PDFMerge\PDFMerger();
                foreach ($tempFiles as $file) {
                    $merger->addPDF($file, 'all');
                }
                
                $outputPath = storage_path('app/temp/merged_' . uniqid() . '.pdf');
                $merger->merge('file', $outputPath);

                // Clean up individual PDFs
                foreach ($tempFiles as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }

                return $outputPath;
            }

            throw new \Exception('No PDFs were generated to merge');
        } catch (\Exception $e) {
            \Log::error("Failed to generate batch report cards: " . $e->getMessage());
            throw new \Exception("Failed to generate batch report cards: " . $e->getMessage());
        }
    }

    private function generateStudentReportCard($reportCard, $allStudentReportCards)
    {
        try {
            // Add debug logging
            \Log::info('Generating student report card', [
                'reportCard' => $reportCard->toArray(),
                'allStudentReportCards' => $allStudentReportCards->toArray()
            ]);

            // Get student details
            $student = $reportCard->student;
            $class = SchoolClass::find($student->school_class_id);

            // Get all students' report cards for this exam and class to calculate rank
            $allClassReportCards = ReportCard::where('exam_id', $reportCard->exam_id)
                ->whereHas('student', function($query) use ($class) {
                    $query->where('school_class_id', $class->id);
                })
                ->with(['student'])
                ->get()
                ->groupBy('student_id');

            // Calculate averages for all students
            $averages = [];
            foreach ($allClassReportCards as $studentId => $cards) {
                $total = $cards->sum('score');
                $count = $cards->count();
                $averages[$studentId] = $count > 0 ? round($total / $count, 2) : 0;
            }

            // Sort averages to determine rank
            arsort($averages);
            $rank = array_search($student->id, array_keys($averages)) + 1;
            $totalStudents = count($averages);

            // Prepare student details
            $student_details = [
                'name' => $student->full_name ?? $student->first_name . ' ' . $student->last_name,
                'admission_number' => $student->admission_number ?? 'N/A',
                'class' => $class->name ?? 'N/A',
                'school_name' => $reportCard->tenant->name ?? 'N/A',
                'school_address' => $reportCard->tenant->address ?? '',
                'school_phone' => $reportCard->tenant->phone ?? '',
                'school_email' => $reportCard->tenant->email ?? '',
                'school_logo' => $reportCard->tenant->logo_url ?? null
            ];

            // Get exam details using the accessors
            $exam_details = [
                'name' => $reportCard->exam->name ?? 'N/A',
                'term' => $reportCard->exam->term_name,
                'academic_year' => $reportCard->exam->academic_year
            ];

            // Process subjects and scores
            $subjects = $allStudentReportCards->map(function($rc) {
                $score = floatval($rc->score);
                return [
                    'name' => $rc->subject->name,
                    'score' => number_format($score, 2),
                    'percentage' => number_format($score, 1),
                    'grade' => $this->calculateGrade($score),
                    'remarks' => $this->generateRemarks($score) // Always use generated remarks, ignore stored ones
                ];
            })->values()->toArray();

            // Add debug logging for final data
            \Log::info('Final subjects data', [
                'subjects' => $subjects,
                'total_score' => $allStudentReportCards->sum('score'),
                'total_subjects' => $allStudentReportCards->count()
            ]);

            // Calculate summary with overall grade
            $total_score = $allStudentReportCards->sum('score');
            $total_subjects = $allStudentReportCards->count();
            $average_score = $total_subjects > 0 ? round($total_score / $total_subjects, 2) : 0;
            $overall_grade = $this->calculateGrade($average_score);

            // Calculate rank
            $class_rankings = ReportCard::where('exam_id', $reportCard->exam_id)
                ->whereHas('student', function($query) use ($class) {
                    $query->where('school_class_id', $class->id);
                })
                ->select('student_id', DB::raw('AVG(score) as average'))
                ->groupBy('student_id')
                ->orderByDesc('average')
                ->get();

            // Calculate the student's rank
            $student_rank = $class_rankings->search(function($item) use ($reportCard) {
                return $item->student_id === $reportCard->student_id;
            }) + 1; // +1 to convert from zero-based index to rank

            // Prepare summary with overall grade
            $summary = [
                'total_score' => $total_score,
                'total_subjects' => $total_subjects,
                'average_score' => $average_score,
                'overall_grade' => $overall_grade,
                'rank' => $student_rank . ' out of ' . $class_rankings->count(), // Update rank format
                'percentage' => number_format($average_score, 1)
            ];

            // Add school details
            $school_details = [
                'name' => $reportCard->tenant->name ?? 'N/A',
                'address' => $reportCard->tenant->address ?? '',
                'phone' => $reportCard->tenant->phone ?? '',
                'email' => $reportCard->tenant->email ?? '',
                'logo' => $reportCard->tenant->logo_url ?? null
            ];

            // Generate PDF
            $pdf = PDF::loadView('pdf.report_card', compact(
                'student_details',
                'exam_details',
                'subjects',
                'summary',
                'school_details'
            ));

            return $pdf->output();

        } catch (\Exception $e) {
            \Log::error('Error in generateStudentReportCard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function getOrdinalSuffix($number) {
        if (!in_array(($number % 100), [11, 12, 13])) {
            switch ($number % 10) {
                case 1:  return $number . 'st';
                case 2:  return $number . 'nd';
                case 3:  return $number . 'rd';
            }
        }
        return $number . 'th';
    }

    private function sanitizeFileName($filename)
    {
        // Remove or replace invalid characters
        $filename = preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename);
        // Ensure filename is not too long
        return substr($filename, 0, 200);
    }

    // Update the downloadZip method in your controller
    public function downloadZip(Request $request)
    {
        try {
            $zipFile = $this->generateZipDownload($reportCards);
            
            return response()->download($zipFile)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
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

    // Add this method to get grade from score
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

    public function getStudentsByClass(Request $request)
    {
        $request->validate([
            'class_id' => 'required|integer|exists:classes,id'
        ]);

        $students = Student::where('school_class_id', $request->query('class_id'))
            ->select('id', 'first_name', 'last_name')
            ->orderBy('first_name')
            ->get();

        return response()->json($students);
    }
}