<?php

use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// --- Guest-only auth routes ---
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store']);

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::post('/logout', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// --- Authenticated routes (any logged-in user) ---
Route::middleware('auth')->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::resource('tasks', TaskController::class)->except(['show']);

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
});

// --- Admin-only routes ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/data', [ReportController::class, 'chartData'])->name('reports.data');
});
