<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\BeltController;


Route::apiResource('students', StudentController::class)
      ->only(['index', 'store']);

Route::apiResource('belts', BeltController::class)
      ->only(['index', 'store']);
