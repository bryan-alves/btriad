<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});
Route::get('/admin/students', function () {
    return view('app');
});
Route::get('/admin/students/create', function () {
    return view('app');
});
Route::get('/admin/students/{id}', function () {
    return view('app');
});
