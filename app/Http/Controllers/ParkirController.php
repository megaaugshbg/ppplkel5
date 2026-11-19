<?php

namespace App\Http\Controllers;

use App\Support\ParkirData;

class ParkirController extends Controller
{
    public function index()
    {
        // urutkan dari yang terdekat
        $lokasi = collect(ParkirData::lokasi())->sortBy('jarak')->values()->all();

        return view('cari-parkir', compact('lokasi'));
    }
}
