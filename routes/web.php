<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ParkirController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\SlotController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Akun: register (insert form) dan login
Route::middleware('guest')->group(function () {
    Route::get('/register', [AppController::class, 'showRegister'])->name('register');
    Route::post('/register', [AppController::class, 'storeRegister']);
    Route::get('/login', [AppController::class, 'showLogin'])->name('login');
    Route::post('/login', [AppController::class, 'login'])->middleware('throttle:10,1');
});
Route::post('/logout', [AppController::class, 'logout'])->middleware('auth')->name('logout');

// Woman Parking dan Cari Lokasi Parkir (boleh dibuka tanpa login)
Route::get('/woman-parking', function () {
    return view('woman-parking');
});
Route::get('/cari-parkir', [ParkirController::class, 'index']);

// Alur penyewa: pilih slot -> booking -> check-in -> check-out -> pembayaran
Route::middleware('auth')->group(function () {
    Route::get('/lokasi/{lokasi}/slot', [SlotController::class, 'pilih']);
    Route::post('/booking', [BookingController::class, 'store']);
    Route::post('/booking/{booking}/check-in', [BookingController::class, 'checkIn']);

    Route::get('/check-out', [CheckoutController::class, 'index']);
    Route::post('/check-out', [CheckoutController::class, 'proses']);

    Route::get('/pembayaran', [PembayaranController::class, 'terbaru']);
    Route::get('/pembayaran/{booking}', [PembayaranController::class, 'index'])->name('pembayaran');
    Route::post('/pembayaran/{booking}', [PembayaranController::class, 'bayar']);
});
