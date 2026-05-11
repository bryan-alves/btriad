<?php

use Illuminate\Support\Facades\Hash;
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
Route::get('/admin/attendance-lists', function () {
    return view('app');
});
Route::get('/admin/attendance-lists/create', function () {
    return view('app');
});
Route::get('/admin/attendance-lists/{id}/edit', function () {
    return view('app');
});
Route::get('/admin/student-graduations', function () {
    return view('app');
});
Route::get('/admin/student-graduations/create', function () {
    return view('app');
});
Route::get('/admin/student-graduations/{id}/edit', function () {
    return view('app');
});


Route::get('login', function () {
    return view('app');
});