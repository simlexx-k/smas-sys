<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\Tenants\ReportCardController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('subjects', 'Api\SubjectController');
    Route::apiResource('classes', SchoolClassController::class);
    Route::apiResource('students', StudentController::class);
    Route::apiResource('attendances', AttendanceController::class);
    Route::apiResource('exams', ExamController::class);
    Route::get('/report-cards/batch-print', [ReportCardController::class, 'batchPrint'])->name('report-cards.batch-print');
    Route::apiResource('report-cards', ReportCardController::class)->except(['show']);
    Route::post('/report-cards/bulk', [ReportCardController::class, 'bulkStore'])->name('report-cards.bulk.store');
    Route::get('/report-cards/{report_card}', [ReportCardController::class, 'show'])->where('report_card', '[0-9]+')->name('report-cards.show');
    Route::get('tenants', [TenantController::class, 'getTenantData']);
});

//Route::middleware('auth:sanctum')->get('/tenants', function () {
//    return response()->json(['tenants' => []]); // Replace with actual data
//});