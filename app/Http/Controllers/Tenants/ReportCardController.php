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
            'type' => 'required|in:individual,nominal,zip',
            'tenant_id' => 'required|exists:tenants,id'
        ]);

        $classId = $request->query('class_id');
        $type = $request->query('type');

        Log::info('Fetching students for class', ['class_id' => $classId]);
        $students = Student::where('school_class_id', $classId)->get();

        if ($students->isEmpty()) {
            Log::warning('No students found for class', ['class_id' => $classId]);
            return response()->json(['error' => 'No students found for this class'], 404);
        }

        Log::info('Fetching report cards for students', ['student_ids' => $students->pluck('id')]);
        $reportCards = ReportCard::whereIn('student_id', $students->pluck('id'))
            ->with(['student', 'exam', 'subject'])
            ->get();

        if ($type === 'individual') {
            Log::info('Generating individual PDFs', ['count' => $reportCards->count()]);
            $pdf = $this->generatePdf($reportCards->first());
            return response()->streamDownload(function() use ($pdf) {
                echo $pdf;
            }, 'report-card.pdf');
        } elseif ($type === 'nominal') {
            Log::info('Generating nominal list PDF');
            return response()->streamDownload(function() use ($reportCards) {
                echo $this->generateNominalListPdf($reportCards);
            }, 'nominal-list.pdf');
        } elseif ($type === 'zip') {
            Log::info('Generating ZIP file');
            $zip = new ZipArchive();
            $zipFileName = storage_path('app/report-cards-' . $classId . '.zip');
            if ($zip->open($zipFileName, ZipArchive::CREATE) === TRUE) {
                foreach ($reportCards as $reportCard) {
                    $this->validateReportCardData($reportCard);
                    $pdf = $this->generatePdf($reportCard);
                    $zip->addFromString($reportCard->student->name . '.pdf', $pdf);
                }
                $zip->close();
                return response()->download($zipFileName)->deleteFileAfterSend(true);
            }
            Log::error('Failed to create ZIP file');
            return response()->json(['error' => 'Failed to create ZIP file'], 500);
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
        $data = $this->sanitizer->sanitize([
            'student' => $reportCard->student->full_name,
            'class' => $reportCard->student->schoolClass->name,
            'exam' => $reportCard->exam->name,
            'subject' => $reportCard->subject->name,
            'score' => $reportCard->score,
            'grade' => $reportCard->grade,
            'remarks' => $reportCard->remarks,
        ]);

        try {
            return Pdf::loadView('pdf.report_card', $data)->output();
        } catch (\Exception $e) {
            Log::error('PDF generation error', ['error' => $e->getMessage(), 'data' => $data]);
            throw new \RuntimeException('Failed to generate PDF');
        }
    }

    private function generateNominalListPdf($reportCards)
    {
        try {
            if (empty($reportCards)) {
                throw new \InvalidArgumentException('No report cards provided');
            }

            // Transform data for the view
            $data = [
                'reportCards' => $reportCards,
                'exam' => $reportCards->first()->exam,
                'tenant' => $reportCards->first()->tenant
            ];

            return Pdf::loadView('pdf.nominal_list', $data)->output();
        } catch (\Exception $e) {
            Log::error('PDF generation error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $reportCards
            ]);
            throw new \RuntimeException('Failed to generate PDF: ' . $e->getMessage());
        }
    }
}
