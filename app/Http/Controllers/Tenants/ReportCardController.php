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

        $classId = $request->query('class_id');
        $examId = $request->query('exam_id');
        $type = $request->query('type');

        if ($type === 'individual') {
            $studentId = $request->query('student_id');
            Log::info('Generating individual PDF', ['student_id' => $studentId]);

            // Get report card for specific student
            $reportCard = ReportCard::where('student_id', $studentId)
                ->where('exam_id', $examId)
                ->with(['student.class', 'exam'])
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
            ->with(['student.class', 'exam'])
            ->get()
            ->map(function($reportCard) use ($examId) {
                // Then get one report card for each student
                return ReportCard::where('student_id', $reportCard->student_id)
                    ->where('exam_id', $examId)
                    ->with(['student.class', 'exam'])
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
        \Log::info('Starting report card generation...');
        
        // Get all report cards for this student and exam to show all subjects
        $allSubjectScores = ReportCard::where('student_id', $reportCard->student_id)
            ->where('exam_id', $reportCard->exam_id)
            ->with(['subject'])
            ->get();

        \Log::info('All subject scores:', [
            'student_id' => $reportCard->student_id,
            'exam_id' => $reportCard->exam_id,
            'scores_count' => $allSubjectScores->count(),
            'scores' => $allSubjectScores->map(fn($rc) => [
                'subject' => $rc->subject->name,
                'score' => $rc->score,
                'grade' => $rc->grade
            ])
        ]);

        // Get student details
        $student = $reportCard->student;
        if (!$student) {
            \Log::error('No student found for report card');
            abort(404, 'Student not found');
        }

        // Get class with null checks
        $class = SchoolClass::find($student->school_class_id);
        if (!$class) {
            \Log::error('No class found for student', [
                'student_id' => $student->id,
                'school_class_id' => $student->school_class_id,
                'student' => $student->toArray()
            ]);
            abort(404, 'Class not found');
        }

        // Prepare student details with null coalescing
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

        // Get exam details with null checks
        $exam_details = [
            'name' => $reportCard->exam->name ?? 'N/A',
            'term' => $reportCard->exam->term ?? 'N/A',
            'year' => $reportCard->exam->year ?? date('Y')
        ];

        // Map all subjects and their scores
        $subjects = $allSubjectScores->map(function($rc) {
            return [
                'name' => $rc->subject->name,
                'score' => number_format($rc->score, 2),
                'grade' => $rc->grade,
                'remarks' => $rc->remarks ?? ''
            ];
        })->values()->toArray();

        \Log::info('Processed subjects:', ['subjects' => $subjects]);

        // Calculate summary with all subjects
        $total_score = $allSubjectScores->sum('score');
        $total_subjects = $allSubjectScores->count();
        $average_score = $total_subjects > 0 ? round($total_score / $total_subjects, 2) : 0;

        // Calculate overall grade based on average
        $overall_grade = $this->calculateOverallGrade($average_score);

        // Get all students' scores in the class for this exam to calculate rank
        $classRankings = ReportCard::select('student_id')
            ->where('exam_id', $reportCard->exam_id)
            ->whereIn('student_id', function($query) use ($student) {
                $query->select('id')
                      ->from('students')
                      ->where('school_class_id', $student->school_class_id);
            })
            ->groupBy('student_id')
            ->get()
            ->map(function($rc) use ($reportCard) {
                // Get average score for each student
                $studentScores = ReportCard::where('student_id', $rc->student_id)
                    ->where('exam_id', $reportCard->exam_id)
                    ->get();
                
                return [
                    'student_id' => $rc->student_id,
                    'average' => $studentScores->average('score')
                ];
            })
            ->sortByDesc('average')
            ->values();

        // Find current student's rank
        $studentRank = $classRankings->search(function($item) use ($reportCard) {
            return $item['student_id'] === $reportCard->student_id;
        });

        $totalStudents = $classRankings->count();

        // Update summary with calculated rank
        $summary = [
            'total_score' => $total_score,
            'total_subjects' => $total_subjects,
            'average_score' => $average_score,
            'overall_grade' => $overall_grade,
            'rank' => ($studentRank !== false) ? ($studentRank + 1) . ' out of ' . $totalStudents : 'N/A',
            'percentage' => $total_subjects > 0 ? ($total_score / ($total_subjects * 100)) * 100 : 0
        ];

        \Log::info('Rankings:', [
            'class_rankings' => $classRankings->toArray(),
            'student_rank' => $studentRank + 1,
            'total_students' => $totalStudents
        ]);

        // Prepare school details
        $school_details = [
            'name' => $reportCard->tenant->name ?? 'N/A',
            'address' => $reportCard->tenant->address ?? '',
            'phone' => $reportCard->tenant->phone ?? '',
            'email' => $reportCard->tenant->email ?? '',
            'logo' => $reportCard->tenant->logo_url ?? null
        ];

        // After preparing all data
        \Log::info('Final data for PDF view:', [
            'student_details' => $student_details,
            'exam_details' => $exam_details,
            'subjects' => $subjects,
            'summary' => $summary,
            'school_details' => $school_details
        ]);

        try {
            $pdf = PDF::loadView('pdf.report_card', compact(
                'student_details',
                'exam_details',
                'subjects',
                'summary',
                'school_details'
            ));
            
            // Return the PDF content instead of streaming it
            return $pdf->output();
        } catch (\Exception $e) {
            \Log::error('PDF generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => [
                    'subjects' => $subjects,
                    'summary' => $summary
                ]
            ]);
            throw $e; // Re-throw to be caught in batchPrint
        }
    }

    // Helper function to calculate overall grade
    private function calculateOverallGrade($average_score)
    {
        if ($average_score >= 80) return 'A';
        if ($average_score >= 65) return 'B';
        if ($average_score >= 50) return 'C';
        return 'D';
    }

    private function generateNominalListPdf($reportCards)
    {
        try {
            if (empty($reportCards)) {
                throw new \InvalidArgumentException('No report cards provided');
            }

            // Get the first report card to access common data
            $firstCard = $reportCards->first();
            
            // Get all subjects for this exam by joining through report_cards
            $subjects = Subject::join('report_cards', 'subjects.id', '=', 'report_cards.subject_id')
                ->where('report_cards.exam_id', $firstCard->exam_id)
                ->where('report_cards.tenant_id', $firstCard->tenant_id)
                ->select('subjects.id', 'subjects.code', 'subjects.name')
                ->distinct()
                ->orderBy('subjects.code')
                ->get();

            // Get all students in the class
            $students = Student::where('school_class_id', $firstCard->student->school_class_id)
                ->orderBy('first_name')
                ->get();

            // Fetch all report cards for this exam and class in one query
            $allReportCards = ReportCard::where('exam_id', $firstCard->exam_id)
                ->whereIn('student_id', $students->pluck('id'))
                ->with(['student', 'subject'])
                ->get()
                ->groupBy('student_id');

            // Initialize arrays to store calculations
            $scores = [];
            $totals = [];
            $averages = [];
            $grades = [];

            // Process scores for each student
            foreach ($students as $student) {
                $studentScores = [];
                $total = 0;
                $subjectCount = 0;

                // Get this student's report cards
                $studentReportCards = $allReportCards->get($student->id, collect([]));

                foreach ($subjects as $subject) {
                    // Find the report card for this subject
                    $reportCard = $studentReportCards->first(function ($card) use ($subject) {
                        return $card->subject_id === $subject->id;
                    });

                    if ($reportCard) {
                        $studentScores[$subject->id] = number_format($reportCard->score, 0);
                        $total += $reportCard->score;
                        $subjectCount++;
                    } else {
                        $studentScores[$subject->id] = '-';
                    }
                }

                $scores[$student->id] = $studentScores;
                $totals[$student->id] = $total;
                
                // Calculate average and format to one decimal place
                $average = $subjectCount > 0 ? $total / $subjectCount : 0;
                $averages[$student->id] = number_format($average, 1);
                
                $grades[$student->id] = $this->calculateOverallGrade($average);
            }

            // Calculate positions based on averages
            $positions = [];
            $sortedAverages = collect($averages)->map(function($avg) {
                return floatval($avg);
            })->sort()->reverse();
            
            $rank = 1;
            $prevAverage = null;
            $skipCount = 0;

            foreach ($sortedAverages as $studentId => $average) {
                if ($prevAverage !== null && $average < $prevAverage) {
                    $rank += $skipCount + 1;
                    $skipCount = 0;
                } elseif ($prevAverage !== null && $average === $prevAverage) {
                    $skipCount++;
                }
                
                $positions[$studentId] = $rank;
                $prevAverage = $average;
            }

            // Sort students by their positions
            $sortedStudents = $students->sort(function ($a, $b) use ($positions) {
                $posA = $positions[$a->id] ?? PHP_INT_MAX;
                $posB = $positions[$b->id] ?? PHP_INT_MAX;
                return $posA - $posB;
            });

            // Get class details
            $class = SchoolClass::find($firstCard->student->school_class_id);

            $data = [
                'reportCards' => $reportCards,
                'exam' => $firstCard->exam,
                'tenant' => $firstCard->tenant,
                'class' => $class,
                'subjects' => $subjects,
                'sortedStudents' => $sortedStudents,
                'scores' => $scores,
                'totals' => $totals,
                'averages' => $averages,
                'grades' => $grades,
                'positions' => $positions
            ];

            return Pdf::loadView('pdf.nominal_list', $data)->output();
        } catch (\Exception $e) {
            Log::error('PDF generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => [
                    'exam_id' => $firstCard->exam_id ?? null,
                    'class_id' => $firstCard->student->school_class_id ?? null
                ]
            ]);
            throw new \RuntimeException('Failed to generate PDF: ' . $e->getMessage());
        }
    }

    public function bulkStore(Request $request)
    {
        $reportCards = $request->validate([
            '*.student_id' => 'required|exists:students,id',
            '*.exam_id' => 'required|exists:exams,id',
            '*.subject_id' => 'required|exists:subjects,id',
            '*.score' => 'required|numeric|between:0,100',
            '*.grade' => 'required|string|max:2',
            '*.remarks' => 'nullable|string|max:255',
            '*.tenant_id' => 'required|exists:tenants,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($reportCards as $cardData) {
                // Check if a report card already exists for this combination
                $existingCard = ReportCard::where([
                    'student_id' => $cardData['student_id'],
                    'exam_id' => $cardData['exam_id'],
                    'subject_id' => $cardData['subject_id'],
                ])->first();

                if ($existingCard) {
                    // Update existing record
                    $existingCard->update($cardData);
                } else {
                    // Create new record
                    ReportCard::create($cardData);
                }
            }
            DB::commit();
            return response()->json(['message' => 'Report cards saved successfully'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving bulk report cards: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save report cards'], 500);
        }
    }

    private function generateBatchReportCards($classId, $examId)
    {
        try {
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
                throw new \InvalidArgumentException('No students found in this class');
            }

            // Get all report cards for this exam and class
            $allReportCards = ReportCard::where('exam_id', $examId)
                ->whereIn('student_id', $students->pluck('id'))
                ->with(['student', 'subject', 'exam', 'tenant'])
                ->get()
                ->groupBy('student_id');

            // Prepare all report card PDFs
            $pdfs = [];
            $tempFiles = [];

            foreach ($students as $student) {
                $studentReportCards = $allReportCards->get($student->id);
                if ($studentReportCards && $studentReportCards->count() > 0) {
                    try {
                        // Generate PDF content for this student
                        $pdf = $this->generateStudentReportCard($studentReportCards->first(), $studentReportCards);
                        
                        // Save to temporary file
                        $tempFile = $tempDir . '/report_card_' . $student->id . '_' . uniqid() . '.pdf';
                        file_put_contents($tempFile, $pdf);
                        $tempFiles[] = $tempFile;
                        $pdfs[] = $tempFile;
                        
                    } catch (\Exception $e) {
                        Log::error('Failed to generate PDF for student', [
                            'student_id' => $student->id,
                            'error' => $e->getMessage()
                        ]);
                        // Continue with other students
                        continue;
                    }
                }
            }

            if (empty($pdfs)) {
                throw new \InvalidArgumentException('No report cards were generated');
            }

            // Merge all PDFs into one
            try {
                $merger = new \Jurosh\PDFMerge\PDFMerger;
                
                foreach ($pdfs as $pdf) {
                    $merger->addPDF($pdf, 'all');
                }

                // Generate the merged PDF
                $mergedPdfPath = $tempDir . '/merged_report_cards_' . uniqid() . '.pdf';
                $merger->merge('file', $mergedPdfPath);

                // Clean up individual PDF files
                foreach ($tempFiles as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }

                // Stream the merged PDF and clean up
                return response()->streamDownload(
                    function() use ($mergedPdfPath) {
                        echo file_get_contents($mergedPdfPath);
                        unlink($mergedPdfPath);
                    },
                    'report_cards.pdf',
                    ['Content-Type' => 'application/pdf']
                );

            } catch (\Exception $e) {
                // Clean up on error
                foreach ($tempFiles as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                    }
                }
                if (isset($mergedPdfPath) && file_exists($mergedPdfPath)) {
                    unlink($mergedPdfPath);
                }
                
                Log::error('PDF merge failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                throw new \RuntimeException('Failed to merge PDFs: ' . $e->getMessage());
            }

        } catch (\Exception $e) {
            Log::error('Batch report card generation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'class_id' => $classId,
                'exam_id' => $examId
            ]);
            throw new \RuntimeException('Failed to generate batch report cards: ' . $e->getMessage());
        }
    }

    private function generateStudentReportCard($reportCard, $allStudentReportCards)
    {
        try {
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

            // Get exam details
            $exam_details = [
                'name' => $reportCard->exam->name ?? 'N/A',
                'term' => $reportCard->exam->term ?? 'N/A',
                'year' => $reportCard->exam->year ?? date('Y')
            ];

            // Process subjects and scores
            $subjects = $allStudentReportCards->map(function($rc) {
                $score = floatval($rc->score);
                return [
                    'name' => $rc->subject->name,
                    'score' => number_format($score, 2),
                    'percentage' => number_format($score, 1),
                    'grade' => $rc->grade,
                    'remarks' => $rc->remarks ?? ''
                ];
            })->values()->toArray();

            // Calculate summary
            $total_score = $allStudentReportCards->sum('score');
            $total_subjects = $allStudentReportCards->count();
            $average_score = $total_subjects > 0 ? round($total_score / $total_subjects, 2) : 0;
            $overall_grade = $this->calculateOverallGrade($average_score);

            $summary = [
                'total_score' => $total_score,
                'total_subjects' => $total_subjects,
                'average_score' => $average_score,
                'overall_grade' => $overall_grade,
                'percentage' => number_format($average_score, 1),
                'rank' => $rank,
                'total_students' => $totalStudents,
                'rank_suffix' => $this->getOrdinalSuffix($rank)
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
            Log::error('Error generating student report card', [
                'student_id' => $reportCard->student_id,
                'error' => $e->getMessage()
            ]);
            throw $e;
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
}
