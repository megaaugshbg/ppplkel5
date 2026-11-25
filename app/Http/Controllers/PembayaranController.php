<?php

namespace App\Http\Controllers;

use App\Support\PesananSesi;
use Illuminate\Http\Request;

// Use case Lakukan Pembayaran (3.4.1)
class PembayaranController extends Controller
{
    // Pemesanan dibayar untuk 1 jam (tarif per jam). Penyewa tidak lagi memilih lama parkir.
    // Nilai 'durasi' tetap disimpan = 1 karena Booking, Check In, dan Check-out masih membacanya.
    public const JAM_DIBAYAR = 1;

    // Form Pembayaran: tampilkan rincian tagihan dari Booking
    public function index()
    {
        $pesanan = PesananSesi::ambil();
        if (! $pesanan) {
            return PesananSesi::arahkanKeBeranda();
        }

        if (PesananSesi::sudahBayar($pesanan)) {
            return redirect()->route('pembayaran.status');
        }

        return view('pembayaran', [
            'pesanan' => $pesanan,
            'total'   => $pesanan['tarif'] * self::JAM_DIBAYAR,
        ]);
    }

    // Proses Pembayaran: simpan data pembayaran, lalu perbarui status booking
    public function bayar(Request $request)
    {
        $pesanan = PesananSesi::ambil();
        if (! $pesanan) {
            return PesananSesi::arahkanKeBeranda();
        }

        if (PesananSesi::sudahBayar($pesanan)) {
            return redirect()->route('pembayaran.status');
        }

        $data = $request->validate([
            'metode' => ['required', 'in:QRIS'],
        ], [
            'metode.required' => 'Pilih metode pembayaran terlebih dahulu.',
            'metode.in'       => 'Metode pembayaran tidak tersedia.',
        ]);

        // Alternate flow 2: pembayaran gagal diproses, status booking tidak berubah
        if ($request->boolean('gagal')) {
            return back()->withInput()->with('error', 'Pembayaran gagal diproses. Silakan coba lagi.');
        }

        $pesanan['durasi'] = self::JAM_DIBAYAR;
        $pesanan['total'] = $pesanan['tarif'] * self::JAM_DIBAYAR;
        $pesanan['metode'] = $data['metode'];
        $pesanan['no_transaksi'] = 'TRX-' . now('Asia/Jakarta')->format('ymdHis');
        $pesanan['waktu_bayar'] = now('Asia/Jakarta')->format('d-m-Y H:i');
        $pesanan['status'] = PesananSesi::SUDAH_BAYAR;

        PesananSesi::simpan($pesanan);

        return redirect()->route('pembayaran.status');
    }

    // Alternate flow 1: Penyewa membatalkan pembayaran -> Halaman Detail Booking
    public function batal()
    {
        return redirect()->route('booking.detail')
            ->with('pesan', 'Pembayaran dibatalkan. Pesananmu belum dibayar.');
    }

    // Halaman Status Pembayaran
    public function status()
    {
        $pesanan = PesananSesi::ambil();
        if (! $pesanan) {
            return PesananSesi::arahkanKeBeranda();
        }

        if (! PesananSesi::sudahBayar($pesanan)) {
            return redirect()->route('pembayaran');
        }

        return view('pembayaran-status', compact('pesanan'));
    }
}
