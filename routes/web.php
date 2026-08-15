<?php

use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TicketController;
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

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');

    // IMPORTANT: literal "/tickets/create" must be registered BEFORE the
    // "/tickets/{ticket}" wildcard route below, or Laravel will try to
    // route-model-bind "create" as a ticket ID and 404.
    Route::middleware('permission:tickets.create')->group(function () {
        Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
        Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    });

    // Claiming/releasing tickets from the shared IT queue.
    Route::middleware('permission:tickets.claim')->group(function () {
        Route::post('/tickets/{ticket}/claim', [TicketController::class, 'claim'])->name('tickets.claim');
        Route::post('/tickets/{ticket}/unclaim', [TicketController::class, 'unclaim'])->name('tickets.unclaim');
    });

    // Updating ticket status / resolving.
    Route::patch('/tickets/{ticket}/status', [TicketController::class, 'updateStatus'])
        ->middleware('permission:tickets.resolve')
        ->name('tickets.status');

    // Wildcard show/comment routes go last so they don't swallow the
    // literal routes above.
    Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
    Route::post('/tickets/{ticket}/comments', [TicketController::class, 'comment'])->name('tickets.comments.store');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
});

// --- Admin-only routes (reports/analytics + account management) ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/data', [ReportController::class, 'chartData'])->name('reports.data');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
});
