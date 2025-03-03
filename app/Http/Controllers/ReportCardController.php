<?php

namespace App\Http\Controllers;

use App\Models\ReportCard;
use Illuminate\Http\Request;
use PDF;

class ReportCardController extends Controller
{
    public function generatePDF(ReportCard $reportCard)
    {
        \Log::info('Starting report card generation in ReportCardController...');
        
        $reportCard->load(['student.school_class', 'exam', 'scores.subject']);
        $tenant = auth()->user()->tenant;
        
        \Log::info('Auth user:', [
            'user_id' => auth()->id(),
            'tenant_id' => auth()->user()->tenant_id ?? 'null'
        ]);

        \Log::info('Raw tenant data:', [
            'tenant' => $tenant ? $tenant->toArray() : 'null'
        ]);

        if (!$tenant) {
            \Log::error('No tenant found for report card generation');
            abort(404, 'Tenant not found');
        }

        // Log tenant details
        \Log::info('Tenant details for report card:', [
            'tenant_id' => $tenant->id,
            'name' => $tenant->name,
            'email' => $tenant->email,
            'phone' => $tenant->phone,
            'address' => $tenant->address,
            'logo_url' => $tenant->logo_url
        ]);

        // Get student details from report card
        $student_details = [
            'name' => $reportCard->student->full_name,
            'admission_number' => $reportCard->student->admission_number,
            'class' => $reportCard->student->school_class->name,
            'school_name' => $tenant->name
        ];

        // Log student details
        \Log::info('Student details for report card:', $student_details);

        // Get exam details
        $exam_details = [
            'name' => $reportCard->exam->name,
            'term' => $reportCard->exam->term,
            'year' => $reportCard->exam->year
        ];

        // Get subjects and scores
        $subjects = $reportCard->scores->map(function($score) {
            return [
                'name' => $score->subject->name,
                'score' => $score->score,
                'grade' => $score->grade,
                'remarks' => $score->remarks
            ];
        })->toArray();

        // Calculate summary
        $total_score = $reportCard->scores->sum('score');
        $total_subjects = $reportCard->scores->count();
        $average_score = $total_subjects > 0 ? round($total_score / $total_subjects, 2) : 0;

        $summary = [
            'total_score' => $total_score,
            'total_subjects' => $total_subjects,
            'average_score' => $average_score,
            'overall_grade' => $reportCard->grade,
            'rank' => $reportCard->rank ?? 'N/A'
        ];

        $viewData = [
            'reportCard' => $reportCard,
            'tenant' => $tenant,
            'student_details' => $student_details,
            'exam_details' => $exam_details,
            'subjects' => $subjects,
            'summary' => $summary
        ];

        \Log::info('Final view data:', $viewData);

        $pdf = PDF::loadView('pdf.report_card', $viewData);

        return $pdf->stream('report-card.pdf');
    }
} 