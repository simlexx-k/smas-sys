<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\TenantController;

Route::domain('{tenant}.'.config('app.domain'))
    ->group(function () {
        Route::get('/', [TenantController::class, 'dashboard'])->name('tenant.dashboard');
        Route::get('/students', [TenantController::class, 'students'])->name('tenant.students');
        Route::get('/teachers', [TenantController::class, 'teachers'])->name('tenant.teachers');
        Route::get('/academics', [TenantController::class, 'academics'])->name('tenant.academics');
        Route::get('/classes', [TenantController::class, 'classes'])->name('tenant.classes');
    });

Route::domain('{tenant}.localhost')->group(function () {
    Route::get('/', [\App\Http\Controllers\TenantController::class, 'dashboard']);
    Route::get('/students', [TenantController::class, 'students'])->name('tenant.students');
    Route::get('/teachers', [TenantController::class, 'teachers'])->name('tenant.teachers');
    Route::get('/academics', [TenantController::class, 'academics'])->name('tenant.academics');
    Route::get('/classes', [TenantController::class, 'classes'])->name('tenant.classes');
});

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Tenant routes
Route::middleware('auth')->group(function () {
    Route::get('tenants', [TenantController::class, 'index'])->name('tenants.index');
    Route::get('tenants/create', [TenantController::class, 'create'])->name('tenants.create');
    Route::post('tenants', [TenantController::class, 'store'])->name('tenants.store');
    Route::get('tenants/{hashedId}/edit', [TenantController::class, 'edit'])->name('tenants.edit');
    Route::get('tenants/academics', [TenantController::class, 'academics'])->name('tenants.academics');
    Route::put('tenants/{hashedId}', [TenantController::class, 'update'])->name('tenants.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
