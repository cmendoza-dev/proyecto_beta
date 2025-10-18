<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\ParticipantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Administrator routes
    Route::middleware('role:Administrator')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // Secretary and Administrator routes
    Route::middleware('role:Secretary|Administrator')->group(function () {
        Route::resource('meetings', MeetingController::class);

        // Rutas adicionales para abrir y cerrar reuniones
        Route::post('/meetings/{meeting}/open', [MeetingController::class, 'open'])->name('meetings.open');
        Route::post('/meetings/{meeting}/close', [MeetingController::class, 'close'])->name('meetings.close');
        Route::get('/meetings/{meeting}/report', [MeetingController::class, 'generateReport'])->name('meetings.report');

        Route::resource('participants', ParticipantController::class);
        Route::get('/attendance/register', [AttendanceController::class, 'register'])->name('attendance.register');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');
    });

    // Default redirect after login
    Route::get('/home', fn () => redirect()->route('meetings.index'))->name('home');

    // Search route for RENIEC
    Route::post('/api/reniec/search', [App\Http\Controllers\ReniecController::class, 'searchByDni'])
        ->middleware('auth')
        ->name('reniec.search');

    // Report generation route (Admin)
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/reports', [App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    });

});
