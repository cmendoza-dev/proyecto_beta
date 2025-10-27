<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Secretary\AttendanceController;
use App\Http\Controllers\Secretary\MeetingController;
use App\Http\Controllers\ParticipantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmailDocumentsController;
use App\Http\Controllers\ReniecController;
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

    Route::get('/documents/download/{meeting}/{filename}', [DocumentController::class, 'download'])->name('documents.download');

    // Administrator routes
    Route::middleware('role:Administrator')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class);
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        // Documentos
        Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');

    });

    Route::get('/meetings/{meeting}/email-data', [EmailDocumentsController::class, 'getEmailData']);
    Route::post('/meetings/{meeting}/email-log', [EmailDocumentsController::class, 'logEmailSend']);
    Route::get('/meetings/{meeting}/email-history', [EmailDocumentsController::class, 'getEmailHistory']);

    // Ruta para enviar documentos por WhatsApp
    Route::post('/meetings/{meeting}/enviar-whatsapp', [WhatsAppController::class, 'enviarWhatsApp'])
    ->name('meetings.enviar-whatsapp');

    // Secretary and Administrator routes
    Route::middleware('role:Secretary|Administrator')->group(function () {
        Route::resource('meetings', MeetingController::class);

        // Rutas adicionales para abrir y cerrar reuniones
        Route::post('/meetings/{meeting}/open', [MeetingController::class, 'open'])->name('meetings.open');
        Route::post('/meetings/{meeting}/close', [MeetingController::class, 'close'])->name('meetings.close');
        Route::get('/meetings/{meeting}/report', [MeetingController::class, 'generateReport'])->name('meetings.report');

        // Documentos
        Route::get('/meetings/{meeting}/documents', [DocumentController::class, 'show'])->name('documents.meeting');
        Route::post('/meetings/{meeting}/documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::post('/meetings/{meeting}/documents/upload', [DocumentController::class, 'upload'])->name('meetings.documents.upload');
        Route::post('/meetings/{meeting}/documents/share-email', [DocumentController::class, 'shareByEmail'])->name('meetings.documents.share.email');
        Route::get('/documents/download/{meeting}/{filename}', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        Route::resource('participants', ParticipantController::class);
        Route::get('/attendance/register', [AttendanceController::class, 'register'])->name('attendance.register');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
        Route::get('/attendance/list', [AttendanceController::class, 'list'])->name('attendance.list');

    });

    // Rutas de perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/password', [ProfileController::class, 'updatePassword'])->name('password.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Default redirect after login
    Route::get('/home', fn () => redirect()->route('meetings.index'))->name('home');

    // Search route for RENIEC
    Route::post('/api/reniec/search', [ReniecController::class, 'searchByDni'])
        ->middleware('auth')
        ->name('reniec.search');

    // Report generation route (Admin)
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });

});
