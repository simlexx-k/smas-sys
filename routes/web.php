<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\Admin\LandlordTenantController;
use App\Http\Controllers\LandlordDashboardController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\PlanController;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/admin-redirect', [TenantController::class, 'adminRedirect'])->name('admin.redirect');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Default dashboard route
    Route::get('/dashboard', [LandlordDashboardController::class, 'index'])
        ->name('dashboard');

    // Landlord (super admin) routes
    Route::middleware(['role:landlord'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('tenants', LandlordTenantController::class);
        Route::resource('subscriptions', SubscriptionController::class);
        
        // New routes for plan management
        Route::prefix('plans')->name('plans.')->group(function () {
            Route::get('/', [PlanController::class, 'index'])->name('index');
            Route::get('/create', [PlanController::class, 'create'])->name('create');
            Route::post('/', [PlanController::class, 'store'])->name('store');
            Route::get('/{plan}/edit', [PlanController::class, 'edit'])->name('edit');
            Route::put('/{plan}', [PlanController::class, 'update'])->name('update');
            Route::delete('/{plan}', [PlanController::class, 'destroy'])->name('destroy');
        });

        // Subscription management routes
        Route::post('tenants/{tenant}/subscriptions', [SubscriptionController::class, 'store'])
            ->name('tenants.subscriptions.store');
        Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])
            ->name('subscriptions.cancel');
        Route::post('subscriptions/{subscription}/renew', [SubscriptionController::class, 'renew'])
            ->name('subscriptions.renew');
        Route::get('subscriptions/reports', [SubscriptionController::class, 'reports'])
            ->name('subscriptions.reports');
    });

    // Tenant admin routes
    Route::middleware(['role:tenant-admin', 'tenant'])->group(function () {
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

        // School Settings routes
        Route::prefix('settings')->group(function () {
            Route::get('/school', [TenantController::class, 'settings'])->name('settings.school');
            Route::put('/school', [TenantController::class, 'updateSettings'])->name('settings.school.update');
        });
    });
});

// API routes
Route::prefix('api')->group(function () {
    Route::apiResource('subjects', \App\Http\Controllers\Api\SubjectController::class);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
