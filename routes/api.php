<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\BeltController;
use App\Http\Controllers\Api\AttendanceListController;
use App\Http\Controllers\Api\StudentGraduationController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\AuthController;

// Rotas públicas
Route::post('/auth/login', [AuthController::class, 'login']);

// Rotas protegidas
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::get('/auth/student/trainings', [AuthController::class, 'studentTrainings']);
    Route::get('/auth/student/graduations', [AuthController::class, 'studentGraduations']);
    Route::post('/auth/student/photo', [AuthController::class, 'updateStudentPhoto']);

    Route::apiResource('students', StudentController::class);
    Route::apiResource('users', UserController::class)
        ->only(['index', 'store', 'show', 'update']);

    Route::apiResource('belts', BeltController::class)
          ->only(['index', 'store']);

    Route::apiResource('attendance-lists', AttendanceListController::class)
          ->only(['index', 'show', 'store', 'update']);

    Route::apiResource('student-graduations', StudentGraduationController::class)
          ->only(['index', 'show', 'store', 'update']);

    Route::apiResource('classes', ClassController::class)
          ->only(['index', 'show', 'store', 'update']);
});
