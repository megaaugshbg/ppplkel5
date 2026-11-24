<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BatalPesananController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\SlotController;
use App\Http\Controllers\WomanParkingController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DetailBookingController;
use App\Http\Controllers\PembayaranController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RiwayatController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', LandingController::class)->name('landing');
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

    // Pilih Tipe Slot, Cari Slot Parkir (denah), Pesan Slot Parkir, Konfirmasi Pesanan
    Route::get('/lokasi/{lokasi}/tipe', [SlotController::class, 'pilihTipe'])->whereNumber('lokasi')->name('lokasi.tipe');
    Route::get('/slot-parkir/{lokasi}', [SlotController::class, 'index'])->whereNumber('lokasi')->name('slot-parkir');
    Route::post('/pesan-slot', [SlotController::class, 'pesan'])->name('pesan-slot');
    Route::get('/konfirmasi-pesanan', [SlotController::class, 'konfirmasi'])->name('konfirmasi-pesanan');


    // Lakukan Pembayaran
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran');
    Route::post('/pembayaran', [PembayaranController::class, 'bayar'])->name('pembayaran.bayar');
    Route::post('/pembayaran/batal', [PembayaranController::class, 'batal'])->name('pembayaran.batal');
    Route::get('/pembayaran/status', [PembayaranController::class, 'status'])->name('pembayaran.status');

    // Lakukan Check In
    Route::get('/check-in', [CheckInController::class, 'index'])->name('check-in');
    Route::post('/check-in', [CheckInController::class, 'proses'])->name('check-in.proses');
    Route::post('/check-in/batal', [CheckInController::class, 'batal'])->name('check-in.batal');
    Route::get('/check-in/konfirmasi', [CheckInController::class, 'konfirmasi'])->name('check-in.konfirmasi');

    // Detail Booking (tujuan saat pembayaran / check in dibatalkan)
    Route::get('/booking/detail', DetailBookingController::class)->name('booking.detail');
    Route::post('/booking/batal', BatalPesananController::class)->name('booking.batal'); // hanya pesanan belum dibayar

    // Riwayat Booking (selesai / dibatalkan)
    Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat');
    Route::get('/riwayat/{kode}', [RiwayatController::class, 'detail'])->where('kode', 'MKR-[0-9]{6}')->name('riwayat.detail');

    // Lakukan Check-out (3.6.1): Booking Aktif -> Scan Barcode -> Berhasil -> Struk
    Route::get('/check-out', [CheckoutController::class, 'index'])->name('check-out');
    Route::get('/check-out/scan', [CheckoutController::class, 'scan'])->name('check-out.scan');
    Route::post('/check-out/scan', [CheckoutController::class, 'proses'])->name('check-out.proses');
    Route::get('/check-out/berhasil', [CheckoutController::class, 'berhasil'])->name('check-out.berhasil');
    Route::get('/check-out/struk', [CheckoutController::class, 'struk'])->name('check-out.struk');

    // Profil (menu Pengaturan): jenis kelamin & kartu disabilitas untuk verifikasi Woman/Priority Parking
    Route::get('/profil', [ProfilController::class, 'index'])->name('profil');
    // Verifikasi SIM (Woman Parking): jenis kelamin hanya dari hasil baca SIM, tidak bisa diubah manual.
    Route::get('/verifikasi-sim', [ProfilController::class, 'verifikasiSim'])->name('verifikasi-sim');
    Route::post('/profil/verifikasi-sim', [ProfilController::class, 'simpanVerifikasiSim'])->middleware('throttle:5,1')->name('profil.verifikasi-sim');
    Route::post('/profil/kartu-disabilitas', [ProfilController::class, 'simpanKartu'])->name('profil.kartu');
    Route::delete('/profil/kartu-disabilitas', [ProfilController::class, 'hapusKartu'])->name('profil.kartu.hapus');
});

// Halaman Cari Lokasi sudah digantikan beranda; tautan lama diarahkan ke /home.
// (Route::redirect membuang query string, jadi ?q= diteruskan manual.)
Route::get('/cari-parkir', fn (Request $request) => redirect()->route('home', $request->query()));
