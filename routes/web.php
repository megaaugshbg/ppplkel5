<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\WomanParkingController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Woman Parking
    Route::get('/woman-parking', [WomanParkingController::class, 'index'])->name('woman-parking.index');
    Route::get('/woman-parking/create', [WomanParkingController::class, 'create'])->name('woman-parking.create');
    Route::post('/woman-parking', [WomanParkingController::class, 'store'])->name('woman-parking.store');
});

// Cari Lokasi Parkir, Cari Slot Parkir, Pesan Slot Parkir
Route::get('/cari-parkir', [\App\Http\Controllers\ParkirController::class, 'index']);
Route::get('/slot-parkir/{lokasi}', [\App\Http\Controllers\SlotController::class, 'index'])->whereNumber('lokasi');
Route::post('/pesan-slot', [\App\Http\Controllers\SlotController::class, 'pesan']);
Route::get('/konfirmasi-pesanan', [\App\Http\Controllers\SlotController::class, 'konfirmasi']);
