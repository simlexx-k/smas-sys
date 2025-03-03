<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\Admin\LandlordTenantController;

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
    Route::get('/bulk-report-cards', [TenantController::class, 'bulkReportCards'])->name('tenant.bulk.report.cards');
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

// Landlord (super admin) routes
Route::middleware(['auth', 'role:super-admin'])->prefix('admin')->group(function () {
    Route::resource('tenants', LandlordTenantController::class);
});

// Tenant admin routes
Route::middleware(['auth', 'role:tenant-admin'])->group(function () {
    Route::get('/dashboard', [TenantController::class, 'dashboard'])->name('dashboard');
    
    // School Settings routes
    Route::prefix('settings')->group(function () {
        Route::get('/school', [TenantController::class, 'settings'])->name('settings.school');
        Route::put('/school', [TenantController::class, 'updateSettings'])->name('settings.school.update');
    });
    
    // ... other tenant routes ...
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
