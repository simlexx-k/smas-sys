<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\Admin\LandlordTenantController;
use App\Http\Controllers\LandlordDashboardController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\DashboardController;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/admin-redirect', [TenantController::class, 'adminRedirect'])->name('admin.redirect');

// Authenticated routes
Route::middleware(['auth', 'verified'])->group(function () {
    // Default dashboard route (will handle redirection)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Landlord routes
    Route::middleware(['role:landlord'])->prefix('landlord')->name('landlord.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'landlord'])->name('dashboard');
    });

    // Tenant routes
    Route::middleware(['role:tenant-admin'])->prefix('tenant')->name('tenant.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'tenant'])->name('dashboard');
    });

    // Existing Landlord (super admin) routes
    Route::middleware(['role:landlord'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('tenants/trash', [LandlordTenantController::class, 'trash'])
            ->name('tenants.trash');

        Route::post('tenants/{tenant}/restore', [LandlordTenantController::class, 'restore'])
            ->name('tenants.restore')
            ->withTrashed();

        Route::delete('tenants/{tenant}/force', [LandlordTenantController::class, 'forceDelete'])
            ->name('tenants.force-delete')
            ->withTrashed();

        Route::get('tenants/export-trash', [LandlordTenantController::class, 'exportTrash'])
            ->name('tenants.export-trash');

        Route::get('/tenants', [LandlordTenantController::class, 'index'])->name('tenants.index');
        Route::post('/tenants/{id}/impersonate', [LandlordTenantController::class, 'impersonate'])
            ->name('tenants.impersonate');
        Route::post('/tenants/{id}/reset-password', [LandlordTenantController::class, 'resetPassword'])
            ->name('tenants.reset-password');
        Route::post('/tenants/{id}/change-plan', [LandlordTenantController::class, 'changePlan'])
            ->name('tenants.change-plan');
        Route::resource('tenants', LandlordTenantController::class);
        Route::resource('subscriptions', SubscriptionController::class);
        
        // Plans management routes
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

        // Domain management
        Route::prefix('tenants/{tenant}')->name('tenants.')->group(function () {
            Route::post('domains', [LandlordTenantController::class, 'storeDomain'])->name('domains.store');
            Route::put('domains/{domain}/primary', [LandlordTenantController::class, 'setPrimaryDomain'])->name('domains.primary');
            
            // Stats routes
            Route::get('stats/usage', [LandlordTenantController::class, 'getUsageStats'])->name('stats.usage');
            Route::get('stats/classes', [LandlordTenantController::class, 'getClassStats'])->name('stats.classes');
            Route::get('stats/students', [LandlordTenantController::class, 'getStudentStats'])->name('stats.students');
        });

        Route::delete('/tenants/{tenant}', [LandlordTenantController::class, 'destroy'])
            ->name('tenants.destroy');
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
