<?php

use App\Http\Controllers\HomeController;
use App\Support\CurrentTenant;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/admin/students', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('/admin/students/create', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('/admin/students/{id}/profile', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
})->where('id', '[0-9]+');
Route::get('/admin/students/{id}/edit', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
})->where('id', '[0-9]+');
Route::get('/admin/students/{id}', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
})->where('id', '[0-9]+');
Route::get('/admin/ranking', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('/admin/attendance-lists', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('/admin/attendance-lists/create', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('/admin/attendance-lists/{id}/edit', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('/admin/student-graduations', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('/admin/student-graduations/create', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('/admin/student-graduations/{id}/edit', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('/admin/users', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('/admin/users/create', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('/admin/users/{id}', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('/admin/site-settings', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});

Route::get('login', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});

Route::get('student', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('student/dashboard', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('student/ranking', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('student/profile', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});

Route::get('/admin/classes', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('/admin/classes/create', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
});
Route::get('/admin/classes/{id}', function () {
    return view('app', ['tenant' => CurrentTenant::get()]);
})->where('id', '[0-9]+');