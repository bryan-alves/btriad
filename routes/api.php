<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BeltController;
use App\Http\Controllers\Api\AttendanceListController;
use App\Http\Controllers\Api\StudentGraduationController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\SiteReviewController;
use App\Http\Middleware\EnsureCanManageSites;
use App\Http\Middleware\EnsureUserBelongsToTenant;

// Rotas públicas
Route::post('/auth/login', [AuthController::class, 'login']);

// Rotas protegidas
Route::middleware(['auth:sanctum', EnsureUserBelongsToTenant::class])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::get('/auth/student/trainings', [AuthController::class, 'studentTrainings']);
    Route::get('/auth/student/graduations', [AuthController::class, 'studentGraduations']);
    Route::post('/auth/student/photo', [AuthController::class, 'updateStudentPhoto']);

    Route::get('students/{student}/trainings', [StudentController::class, 'trainings']);
    Route::get('students/{student}/graduations', [StudentController::class, 'graduations']);
    Route::apiResource('students', StudentController::class);
    Route::apiResource('users', UserController::class)
        ->only(['index', 'store', 'show', 'update']);

    Route::apiResource('belts', BeltController::class)
          ->only(['index', 'store']);

    Route::get('attendance-lists/ranking-periods', [AttendanceListController::class, 'rankingPeriods']);
    Route::get('attendance-lists/ranking', [AttendanceListController::class, 'ranking']);

    Route::apiResource('attendance-lists', AttendanceListController::class)
          ->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::apiResource('student-graduations', StudentGraduationController::class)
          ->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::get('classes/schedule', [ClassController::class, 'schedule']);
    Route::apiResource('classes', ClassController::class)
          ->only(['index', 'show', 'store', 'update']);

    Route::middleware(EnsureCanManageSites::class)->group(function () {
        Route::get('site-settings', [SiteSettingController::class, 'index']);
        Route::post('site-settings/{tenant}', [SiteSettingController::class, 'update']);
        Route::put('site-settings/{tenant}', [SiteSettingController::class, 'update']);
        Route::apiResource('site-reviews', SiteReviewController::class)
            ->only(['index', 'store', 'update', 'destroy']);
    });
});
