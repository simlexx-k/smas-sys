<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\TenantController; 
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');

    Route::get('settings/tenant-management', function () {
        return Inertia::render('settings/TenantManagement');
    })->name('tenant-management');

    Route::get('settings/tenant-management/tenants', [TenantController::class, 'getTenantsForAdmin'])->name('tenant-management.tenants');
    Route::get('settings/tenant-management/tenants-for-admin', [TenantController::class, 'getTenantsForAdmin'])->name('tenant-management.tenants-for-admin');
});
