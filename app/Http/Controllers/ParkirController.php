<?php

namespace App\Http\Controllers;

class ParkirController extends Controller
{
    public function index()
    {
        $lokasi = [
            ['nama' => 'Parkir Mall Galaxy', 'alamat' => 'Jl. Dharmahusada Indah, Surabaya', 'tipe' => ['reguler', 'wanita'], 'tarif' => 5000, 'slot' => 24, 'jarak' => 0.8, 'buka' => true],
            ['nama' => 'Parkir Kampus ITS', 'alamat' => 'Jl. Raya ITS, Sukolilo, Surabaya', 'tipe' => ['reguler'], 'tarif' => 3000, 'slot' => 40, 'jarak' => 1.5, 'buka' => true],
            ['nama' => 'Parkir Pakuwon Mall', 'alamat' => 'Jl. Mayjend Yono Soewoyo, Surabaya', 'tipe' => ['reguler', 'wanita'], 'tarif' => 6000, 'slot' => 12, 'jarak' => 4.2, 'buka' => false],
        ];

        // urutkan dari yang terdekat
        $lokasi = collect($lokasi)->sortBy('jarak')->values()->all();

        return view('cari-parkir', compact('lokasi'));
    }
}