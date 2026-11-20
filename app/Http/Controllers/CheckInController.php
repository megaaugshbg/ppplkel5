<?php

namespace App\Http\Controllers;

use App\Models\LokasiParkir;
use App\Models\SlotParkir;
use App\Support\PesananSesi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

// Use case Lakukan Check In (3.4.2)
class CheckInController extends Controller
{
    // Halaman Check In
    public function index()
    {
        $pesanan = PesananSesi::ambil();

        // check in hanya bisa dilakukan setelah pesanan dibayar
        if (! PesananSesi::sudahBayar($pesanan)) {
            return redirect()->route('pembayaran')
                ->with('error', 'Selesaikan pembayaran terlebih dahulu sebelum check in.');
        }

        if (PesananSesi::sudahCheckIn($pesanan)) {
            return redirect()->route('check-in.konfirmasi');
        }

        return view('check-in', compact('pesanan'));
    }

    // Proses Check In: catat waktu check in, lalu perbarui status slot jadi terisi
    public function proses(Request $request)
    {
        $pesanan = PesananSesi::ambil();

        if (! PesananSesi::sudahBayar($pesanan)) {
            return redirect()->route('pembayaran');
        }
        if (PesananSesi::sudahCheckIn($pesanan)) {
            return redirect()->route('check-in.konfirmasi');
        }

        // Alternate flow 2: check in gagal diproses, data booking & slot tidak berubah
        if ($request->boolean('gagal')) {
            return back()->with('error', 'Check in gagal diproses. Silakan coba lagi.');
        }

        try {
            DB::transaction(function () use ($pesanan) {
                $this->perbaruiStatusSlot($pesanan);
            });
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Check in gagal diproses. Silakan coba lagi.');
        }

        $pesanan['waktu_checkin'] = now('Asia/Jakarta')->format('d-m-Y H:i');
        $pesanan['status'] = PesananSesi::SUDAH_CHECKIN;
        PesananSesi::simpan($pesanan);

        return redirect()->route('check-in.konfirmasi');
    }

    // Alternate flow 1: Penyewa membatalkan check in -> Halaman Detail Booking
    public function batal()
    {
        return redirect()->route('booking.detail')
            ->with('pesan', 'Check in dibatalkan. Kamu bisa check in lagi saat sudah tiba di lokasi.');
    }

    // Halaman Konfirmasi Check In
    public function konfirmasi()
    {
        $pesanan = PesananSesi::ambil();

        if (! PesananSesi::sudahCheckIn($pesanan)) {
            return redirect()->route('check-in');
        }

        return view('check-in-konfirmasi', compact('pesanan'));
    }

    // Perbarui Status Slot: slot parkir pesanan ini menjadi terisi
    private function perbaruiStatusSlot(array $pesanan): void
    {
        // data contoh tidak mengubah database bersama
        if (! empty($pesanan['contoh'])) {
            return;
        }

        $lokasi = LokasiParkir::where('nama', $pesanan['lokasi'])
            ->where('alamat', $pesanan['alamat'])
            ->first();

        if (! $lokasi) {
            return;
        }

        SlotParkir::where('lokasi_parkir_id', $lokasi->id)
            ->where('kode', $pesanan['slot'])
            ->update(['status' => SlotParkir::STATUS_TERISI]);
    }
}
