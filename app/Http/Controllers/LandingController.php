<?php

namespace App\Http\Controllers;

use App\Models\LokasiParkir;

// Halaman depan untuk pengunjung yang belum login
class LandingController extends Controller
{
    public function __invoke()
    {
        $mal = LokasiParkir::orderBy('nama')->take(6)->get();

        return view('landing', [
            'mal'        => $mal,
            'jumlahMal'  => LokasiParkir::count(),
            'jumlahKota' => LokasiParkir::distinct()->count('kota'),
        ]);
    }
}
