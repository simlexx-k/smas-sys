<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TenantController;

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/', [TenantController::class, 'dashboard'])->name('tenant.dashboard');
    Route::get('/students', [TenantController::class, 'students'])->name('tenant.students');
    Route::get('/teachers', [TenantController::class, 'teachers'])->name('tenant.teachers');
    Route::get('/academics', [TenantController::class, 'academics'])->name('tenant.academics');
    Route::get('/classes', [TenantController::class, 'classes'])->name('tenant.classes');
    Route::get('/attendance', [TenantController::class, 'attendance'])->name('tenant.attendance');
    Route::get('/attendance-admin', [TenantController::class, 'attendanceAdmin'])->name('tenant.attendance.admin');
});

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin-redirect', [TenantController::class, 'adminRedirect'])->name('admin.redirect');

// Tenant routes
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('tenants', [TenantController::class, 'index'])->name('tenants.index');
    Route::get('tenants/create', [TenantController::class, 'create'])->name('tenants.create');
    Route::post('tenants', [TenantController::class, 'store'])->name('tenants.store');
    Route::get('tenants/{hashedId}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
    Route::get('tenants/academics', [TenantController::class, 'academics'])->name('tenants.academics');
    Route::put('tenants/{hashedId}', [TenantController::class, 'update'])->name('tenants.update');
    Route::get('tenants/{hashedId}/admin-redirect', [TenantController::class, 'adminRedirect'])->name('tenants.admin.redirect');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
