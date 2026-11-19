<?php

namespace App\Http\Controllers;

use App\Support\ParkirData;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    // Cari Slot Parkir (3.3.7): denah lantai dan status slot
    public function index(Request $request, int $lokasi)
    {
        $data = ParkirData::lokasi()[$lokasi] ?? null;
        abort_if(! $data, 404);

        $tipe = $request->query('tipe', 'reguler');
        if (! array_key_exists($tipe, ParkirData::tipe())) {
            $tipe = 'reguler';
        }

        // lokasi di luar jam operasional
        if (! $data['buka']) {
            return redirect('/cari-parkir')
                ->with('pesan', 'Lokasi parkir sedang tutup. Silakan pilih lahan parkir lain.');
        }

        $slots = ParkirData::slots($lokasi, $tipe);
        $tersedia = count(array_filter($slots, fn ($s) => ! $s['terisi']));

        return view('slot-parkir', [
            'lokasi' => $data,
            'tipe' => $tipe,
            'labelTipe' => ParkirData::tipe()[$tipe],
            'perLantai' => collect($slots)->groupBy('lantai'),
            'tersedia' => $tersedia,
        ]);
    }

    // Pesan Slot Parkir (3.3.8)
    public function pesan(Request $request)
    {
        $idLokasi = (int) $request->input('lokasi');
        $tipe = $request->input('tipe', 'reguler');
        if (! array_key_exists($tipe, ParkirData::tipe())) {
            $tipe = 'reguler';
        }
        $kode = $request->input('slot');
        $balik = '/slot-parkir/' . $idLokasi . '?tipe=' . $tipe;

        $lokasi = ParkirData::lokasi()[$idLokasi] ?? null;
        abort_if(! $lokasi, 404);

        if (! $kode) {
            return redirect($balik)->with('error', 'Pilih slot parkir terlebih dahulu.');
        }

        $slot = collect(ParkirData::slots($idLokasi, $tipe))->firstWhere('kode', $kode);
        if (! $slot) {
            return redirect($balik)->with('error', 'Slot parkir tidak ditemukan.');
        }

        $key = $idLokasi . '-' . $kode;

        // verifikasi ketersediaan slot
        if ($slot['terisi'] || in_array($key, ParkirData::rebutan(), true)) {
            session()->push('slot_terisi', $key);

            return redirect($balik)->with('error', 'Slot telah dipesan pengguna lain. Silakan pilih slot lain.');
        }

        // simulasi gagal menyimpan pesanan
        if ($request->boolean('gagal')) {
            return redirect($balik)->with('error', 'Terjadi kesalahan saat menyimpan pesanan. Silakan coba lagi.');
        }

        // simpan pemesanan (sementara di session, belum ke database)
        session()->push('slot_terisi', $key);
        session()->put('pesanan_terakhir', [
            'kode' => 'MKR-' . str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT),
            'lokasi' => $lokasi['nama'],
            'alamat' => $lokasi['alamat'],
            'tipe' => ParkirData::tipe()[$tipe],
            'slot' => $kode,
            'tarif' => $lokasi['tarif'],
            'status' => 'Terpesan',
        ]);

        return redirect('/konfirmasi-pesanan');
    }

    public function konfirmasi()
    {
        $pesanan = session('pesanan_terakhir');
        if (! $pesanan) {
            return redirect('/cari-parkir');
        }

        return view('konfirmasi-pesanan', compact('pesanan'));
    }
}
