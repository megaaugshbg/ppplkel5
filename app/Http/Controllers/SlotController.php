<?php

namespace App\Http\Controllers;

use App\Models\LokasiParkir;
use App\Models\SlotParkir;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    /** Halaman Pilih Slot: daftar slot kosong + form booking. */
    public function pilih(Request $request, LokasiParkir $lokasi)
    {
        if (! $lokasi->buka) {
            return redirect('/cari-parkir')->with('error', 'Lokasi parkir sedang tutup. Silakan pilih lahan parkir lain.');
        }

        $tipe = in_array($request->query('tipe'), SlotParkir::TIPE, true)
            ? $request->query('tipe')
            : SlotParkir::TIPE_REGULER;

        $slots = $lokasi->slots()
            ->where('tipe', $tipe)
            ->tersedia()
            ->orderBy('kode')
            ->get();

        return view('pilih-slot', compact('lokasi', 'tipe', 'slots'));
    }
}
