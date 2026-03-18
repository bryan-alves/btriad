<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\BeltController;


Route::apiResource('students', StudentController::class);

Route::apiResource('belts', BeltController::class)
      ->only(['index', 'store']);
