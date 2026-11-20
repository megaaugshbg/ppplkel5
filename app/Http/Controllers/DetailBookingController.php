<?php

namespace App\Http\Controllers;

use App\Support\PesananSesi;

// Halaman Detail Booking (tujuan saat pembayaran / check in dibatalkan)
class DetailBookingController extends Controller
{
    public function __invoke()
    {
        $pesanan = PesananSesi::ambil();

        return view('detail-booking', [
            'pesanan'      => $pesanan,
            'sudahBayar'   => PesananSesi::sudahBayar($pesanan),
            'sudahCheckIn' => PesananSesi::sudahCheckIn($pesanan),
        ]);
    }
}
