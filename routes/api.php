<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceController;

Route::apiResource('classes', \App\Http\Controllers\SchoolClassController::class);
Route::apiResource('students', StudentController::class);
Route::apiResource('attendances', \App\Http\Controllers\AttendanceController::class);
