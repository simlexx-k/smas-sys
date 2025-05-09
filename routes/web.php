<?php

use App\Http\Controllers\TermController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\Admin\LandlordTenantController;
use App\Http\Controllers\LandlordDashboardController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\ClassController as TeacherClassController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\Teacher\ExamResultsController;
use App\Http\Controllers\Teacher\ReportController;
use App\Http\Controllers\Auth\SchoolRegistrationController;
use App\Http\Controllers\ChatController;
use App\Models\Plan;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// School Registration Routes
Route::get('/register/school', function () {
    return Inertia::render('auth/RegisterSchool');
})->name('register.school');

Route::post('/register/school', [SchoolRegistrationController::class, 'store'])
    ->name('register.school.store');

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
        Route::get('tenants/{tenant}/invoices/{invoice}/download', 
            [InvoiceController::class, 'download'])
            ->name('admin.tenants.invoices.download')
            ->whereNumber(['tenant', 'invoice'])
            ->middleware(['auth', 'can:download,invoice']);

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

        // Add this before the resource route to handle edit view
        Route::get('admin/subscriptions/{subscription}/edit', [\App\Http\Controllers\Admin\SubscriptionController::class, 'edit'])
            ->name('admin.subscriptions.edit');

        // Keep your existing resource route with exceptions
        Route::resource('admin/subscriptions', \App\Http\Controllers\Admin\SubscriptionController::class)
            ->except(['create', 'show', 'destroy']);

        // Correct invoice generation route
        Route::post('/invoices/{invoice}/generate-pdf', [InvoiceController::class, 'generatePdf'])
            ->middleware(['auth', 'can:generate,App\Models\Invoice'])
            ->name('invoices.generate-pdf');

        // Add this invoice route inside the admin group
        Route::post('tenants/{tenant}/invoices/{invoice}/generate-pdf', [InvoiceController::class, 'generatePdf'])
            ->middleware(['auth', 'can:generate,invoice'])
            ->name('tenants.invoices.generate-pdf')
            ->whereNumber(['tenant', 'invoice']);
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
        Route::get('/terms', function () {
            return Inertia::render('Tenants/Terms');
        })->name('tenant.terms');
        Route::get('/batch-print-report-cards', function () {
            return Inertia::render('Tenants/BatchPrintReportCards');
        })->name('batch-print-report-cards');
        Route::get('/bulk-report-cards', [TenantController::class, 'bulkReportCards'])->name('tenant.bulk.report.cards');

        // School Settings routes
        Route::prefix('settings')->group(function () {
            Route::get('/school', [TenantController::class, 'settings'])->name('settings.school');
            Route::put('/school', [TenantController::class, 'updateSettings'])->name('settings.school.update');
        });

        // Teacher management routes
        Route::prefix('teachers')->name('tenant.teachers.')->group(function () {
            Route::get('/', [TenantController::class, 'teachers'])->name('index');
            Route::post('/', [TeacherController::class, 'store'])->name('store');
            Route::get('/{teacher}', [TeacherController::class, 'show'])->name('show');
            Route::put('/{teacher}', [TeacherController::class, 'update'])->name('update');
            Route::delete('/{teacher}', [TeacherController::class, 'destroy'])->name('destroy');
        });
    });

    // Teacher routes
    Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])
            ->name('dashboard');
        
        Route::get('/classes', [TeacherClassController::class, 'index'])
            ->name('classes.index');
        
        Route::get('/classes/{class}', [TeacherClassController::class, 'show'])
            ->name('classes.show');
        
        Route::get('/classes/{class}/attendance', [TeacherAttendanceController::class, 'create'])
            ->name('attendance.create');
        
        Route::post('/classes/{class}/attendance', [TeacherAttendanceController::class, 'store'])
            ->name('attendance.store');
    });

    Route::get('/teacher/students', [TeacherDashboardController::class, 'students'])
        ->middleware(['auth', 'role:teacher'])
        ->name('teacher.students');

    Route::prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/reports/academic', [ReportController::class, 'generateAcademicReport'])->name('reports.academic');
        Route::post('/reports/attendance', [ReportController::class, 'generateAttendanceReport'])->name('reports.attendance');
        Route::get('/reports/download-pdf', [ReportController::class, 'downloadPDF'])->name('reports.download-pdf');
    });

    Route::middleware(['auth'])->group(function () {
        Route::post('/chat', [ChatController::class, 'store'])->name('chat.store');
        Route::get('/chat/history', [ChatController::class, 'history'])->name('chat.history');
    });
});

// API routes
Route::prefix('api')->group(function () {
    Route::apiResource('subjects', \App\Http\Controllers\Api\SubjectController::class);
});

Route::middleware(['auth', 'verified', 'role:teacher'])->group(function () {
    Route::get('/teacher/exam-results', [ExamResultsController::class, 'index'])->name('teacher.exam-results');
    Route::get('/teacher/exam-results/get', [ExamResultsController::class, 'getResults'])->name('teacher.exam-results.get');
    Route::post('/teacher/exam-results', [ExamResultsController::class, 'store'])->name('teacher.exam-results.store');
});

Route::get('/admin/plans/active', function () {
    return response()->json(
        Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get()
    );
})->name('admin.plans.active');

// Add this temporary route ABOVE all others
Route::get('/invoices/{filename}', function ($filename) {
    $path = storage_path('app/invoices/'.$filename);
    
    if (!file_exists($path)) {
        abort(404);
    }

    return response()->download($path, $filename, [
        'Content-Type' => 'application/pdf',
        'Cache-Control' => 'no-store, max-age=0'
    ]);
})->where('filename', '.*\.pdf$');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
