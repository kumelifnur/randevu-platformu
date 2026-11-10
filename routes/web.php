<?php

use App\Http\Controllers\AppointmentStatusController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BusinessHoursController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SessionController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'home')->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/kayit', [RegistrationController::class, 'create'])->name('register');
    Route::post('/kayit', [RegistrationController::class, 'store'])->name('register.store');
    Route::get('/giris', [SessionController::class, 'create'])->name('login');
    Route::post('/giris', [SessionController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
});

Route::post('/cikis', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'business.owner'])->prefix('panel')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::post('/hizmetler', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/hizmetler/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/hizmetler/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
    Route::put('/calisma-saatleri', [BusinessHoursController::class, 'update'])->name('hours.update');
    Route::patch('/randevular/{appointment}/durum', [AppointmentStatusController::class, 'update'])->name('appointments.status');
});

Route::get('/r/{business}', [BookingController::class, 'show'])->name('booking.show');
Route::post('/r/{business}/randevu', [BookingController::class, 'store'])->name('booking.store');
Route::get('/r/{business}/onay/{appointmentId}', [BookingController::class, 'confirmation'])->name('booking.confirmation');
