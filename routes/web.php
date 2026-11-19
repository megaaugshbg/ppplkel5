<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SlotController;
use App\Http\Controllers\WomanParkingController;
use Illuminate\Http\Request;
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

    // Pilih Tipe Slot, Cari Slot Parkir (denah), Pesan Slot Parkir
    Route::get('/lokasi/{lokasi}/tipe', [SlotController::class, 'pilihTipe'])->whereNumber('lokasi')->name('lokasi.tipe');
    Route::get('/slot-parkir/{lokasi}', [SlotController::class, 'index'])->whereNumber('lokasi')->name('slot-parkir');
    Route::post('/pesan-slot', [SlotController::class, 'pesan'])->name('pesan-slot');
});

// Halaman Cari Lokasi sudah digantikan beranda; tautan lama diarahkan ke /home.
// (Route::redirect membuang query string, jadi ?q= diteruskan manual.)
Route::get('/cari-parkir', fn (Request $request) => redirect()->route('home', $request->query()));
Route::get('/konfirmasi-pesanan', [SlotController::class, 'konfirmasi']);
