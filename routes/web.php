<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TenantController;

// All routes
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/', [TenantController::class, 'dashboard'])->name('tenant.dashboard');
    Route::get('/students', [TenantController::class, 'students'])->name('tenant.students');
    Route::get('/teachers', [TenantController::class, 'teachers'])->name('tenant.teachers');
    Route::get('/academics', [TenantController::class, 'academics'])->name('tenant.academics');
    Route::get('/classes', [TenantController::class, 'classes'])->name('tenant.classes');
    Route::get('/attendance', [TenantController::class, 'attendance'])->name('tenant.attendance');
    Route::get('/attendance-admin', [TenantController::class, 'attendanceAdmin'])->name('tenant.attendance.admin');
    Route::get('/report-cards', [TenantController::class, 'reportCards'])->name('tenant.report.cards');
    Route::get('/exams', [TenantController::class, 'exams'])->name('tenant.exams');
    Route::get('/subjects', [TenantController::class, 'subjects'])->name('tenant.subjects');
    Route::get('/manage-subject/{id?}', [TenantController::class, 'manageSubject'])->name('manage-subject');
    Route::get('/batch-print-report-cards', function () {
        return Inertia::render('Tenants/BatchPrintReportCards');
    })->name('batch-print-report-cards');
});

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin-redirect', [TenantController::class, 'adminRedirect'])->name('admin.redirect');

Route::prefix('api')->group(function () {
    Route::apiResource('subjects', \App\Http\Controllers\Api\SubjectController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
