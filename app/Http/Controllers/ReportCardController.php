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
        $subjects = collect([
            [
                'name' => $reportCard->subject->name,
                'score' => floatval($reportCard->score),
                'grade' => $this->calculateGrade(floatval($reportCard->score)),
                'remarks' => $this->generateRemarks(floatval($reportCard->score))
            ]
        ])->toArray();

        // Calculate summary
        $total_score = floatval($reportCard->score);
        $total_subjects = 1;
        $average_score = $total_score;

        $summary = [
            'total_score' => $total_score,
            'total_subjects' => $total_subjects,
            'average_score' => $average_score,
            'overall_grade' => $this->calculateGrade($average_score),
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

    private function calculateGrade($score) {
        switch (true) {
            case $score >= 80:
                return 'A';
            case $score >= 70:
                return 'B';
            case $score >= 60:
                return 'C';
            case $score >= 50:
                return 'D';
            case $score >= 40:
                return 'E';
            default:
                return 'F';
        }
    }

    private function generateRemarks($score) {
        switch (true) {
            case $score >= 90:
                return "Exceptional mastery of subject. Shows deep understanding and excellent analytical skills.";
            case $score >= 80:
                return "Strong performance. Demonstrates thorough understanding of concepts.";
            case $score >= 70:
                return "Good grasp of subject matter. Shows consistent effort and understanding.";
            case $score >= 60:
                return "Satisfactory performance. More practice needed in some areas.";
            case $score >= 50:
                return "Fair understanding. Needs to focus on improving key concepts.";
            case $score >= 40:
                return "Below average. Requires additional support and dedicated practice.";
            case $score >= 30:
                return "Significant improvement needed. Recommend remedial classes.";
            default:
                return "Critical attention required. Parent-teacher meeting recommended.";
        }
    }
} 