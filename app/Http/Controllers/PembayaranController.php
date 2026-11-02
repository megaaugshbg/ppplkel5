<?php

namespace App\Http\Controllers;

class PembayaranController extends Controller
{
    public function index()
    {
        $booking = [
            'kode' => 'MKR-001',
            'lokasi' => 'Parkir Mall Galaxy',
            'slot' => 'A-12',
            'tanggal' => '3 November 2026',
            'waktu' => '10:00 - 12:00',
            'durasi' => 2,
            'tarif' => 5000,
            'status' => 'Belum dibayar',
        ];

        $total = $booking['tarif'] * $booking['durasi'];

        $metode = ['QRIS', 'GoPay', 'OVO', 'DANA', 'Transfer Bank'];

        return view('pembayaran', compact('booking', 'total', 'metode'));
    }
}
