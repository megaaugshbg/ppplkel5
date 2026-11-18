<?php

namespace App\Http\Controllers;

use App\Models\LokasiParkir;
use App\Models\SlotParkir;
use Illuminate\Http\Request;

class ParkirController extends Controller
{
    public function index(Request $request)
    {
        // slot yang masih dipakai booking aktif tidak dihitung tersedia
        $lokasi = LokasiParkir::with(['slots.bookings' => fn ($q) => $q->aktif()])
            ->orderBy('jarak') // terdekat lebih dulu
            ->get()
            ->map(function (LokasiParkir $l) {
                $tersedia = $l->slots->filter(fn ($s) => $s->bookings->isEmpty());

                return [
                    'id'     => $l->id,
                    'nama'   => $l->nama,
                    'alamat' => $l->alamat,
                    'tarif'  => $l->tarif,
                    'jarak'  => $l->jarak,
                    'buka'   => $l->buka,
                    'tipe'   => $l->slots->pluck('tipe')->unique()->values()->all(),
                    'slot'   => [
                        SlotParkir::TIPE_REGULER => $tersedia->where('tipe', SlotParkir::TIPE_REGULER)->count(),
                        SlotParkir::TIPE_WANITA  => $tersedia->where('tipe', SlotParkir::TIPE_WANITA)->count(),
                    ],
                ];
            })
            ->all();

        $tipeAwal = in_array($request->query('tipe'), SlotParkir::TIPE, true)
            ? $request->query('tipe')
            : SlotParkir::TIPE_REGULER;

        return view('cari-parkir', compact('lokasi', 'tipeAwal'));
    }
}
