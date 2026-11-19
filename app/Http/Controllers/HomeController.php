<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        $destinasi = [
            ['nama' => 'Senayan City',    'kota' => 'Jakarta'],
            ['nama' => 'Lippo Mall Puri', 'kota' => 'Jakarta Barat'],
            ['nama' => 'AEON Mall',       'kota' => 'Bogor'],
        ];

        return view('home', compact('destinasi'));
    }
}
