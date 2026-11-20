<?php

namespace App\Http\Controllers;

use App\Support\PesananSesi;
use Illuminate\Http\RedirectResponse;

/**
 * Batalkan pesanan aktif yang BELUM dibayar (status terpesan): booking menjadi
 * dibatalkan dan slot kembali tersedia. Pesanan yang sudah dibayar tidak bisa
 * dibatalkan di sini (belum ada alur pengembalian dana).
 *
 * Catatan: PembayaranController@batal & CheckInController@batal hanya keluar dari
 * form (kembali ke Detail Booking), tidak membatalkan booking.
 */
class BatalPesananController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $pesanan = PesananSesi::ambil();
        if (! $pesanan) {
            return PesananSesi::arahkanKeBeranda();
        }

        if (PesananSesi::sudahBayar($pesanan)) {
            return redirect()->route('booking.detail')
                ->with('pesan', 'Pesanan yang sudah dibayar tidak bisa dibatalkan.');
        }

        $kode = PesananSesi::kosongkan();

        return redirect()->route('home')
            ->with('pesan', $kode ? "Pesanan {$kode} dibatalkan." : 'Tidak ada pesanan yang bisa dibatalkan.');
    }
}
