<?php

namespace App\Http\Controllers;

class CheckoutController extends Controller
{
    public function index()
    {
        // booking aktif milik Penyewa (data contoh)
        $booking = [
            'lokasi' => 'Parkir Mall Galaxy',
            'slot' => 'A-12',
            'kode' => 'MKR-001',
        ];

        // data contoh untuk simulasi validasi barcode
        $dataBooking = [
            'MKR-001' => [
                'lokasi' => 'Parkir Mall Galaxy',
                'slot' => 'A-12',
                'check_in' => '10:00',
                'keluar' => '12:00',
                'durasi' => 2,
                'tarif' => 5000,
                'sudah_checkin' => true,
            ],
            'MKR-002' => [
                'lokasi' => 'Parkir Kampus ITS',
                'slot' => 'B-03',
                'check_in' => '-',
                'keluar' => '-',
                'durasi' => 0,
                'tarif' => 3000,
                'sudah_checkin' => false,
            ],
        ];

        return view('check-out', compact('booking', 'dataBooking'));
    }
}
