<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    /** Halaman Booking Aktif milik penyewa yang sedang login. */
    public function index(Request $request)
    {
        $booking = Booking::with('slot.lokasi')
            ->where('user_id', $request->user()->id)
            ->aktif()
            ->latest('id')
            ->first();

        return view('check-out', compact('booking'));
    }

    /** Validasi barcode (kode booking), lalu catat check-out. Dipanggil lewat fetch dari halaman. */
    public function proses(Request $request)
    {
        $kode = strtoupper(trim((string) $request->input('kode')));

        if (! preg_match('/^MKR-[0-9]{3,}$/', $kode)) {
            return response()->json(['pesan' => 'Barcode tidak valid. Silakan lakukan pemindaian ulang.'], 422);
        }

        return DB::transaction(function () use ($request, $kode) {
            $booking = Booking::with('slot.lokasi')
                ->where('kode', $kode)
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->first();

            if (! $booking) {
                return response()->json(['pesan' => 'Booking tidak ditemukan. Silakan periksa kembali barcode yang digunakan.'], 404);
            }
            if ($booking->status === Booking::DIPESAN) {
                return response()->json(['pesan' => 'Anda belum melakukan check-in, sehingga check-out tidak dapat dilakukan.'], 422);
            }
            if ($booking->status !== Booking::PARKIR) {
                return response()->json(['pesan' => 'Booking ini sudah di-check-out.'], 422);
            }

            $keluar = now();
            // dibulatkan ke atas per jam, minimal 1 jam
            $durasi = max(1, (int) ceil($booking->check_in_at->diffInMinutes($keluar) / 60));
            $total  = $durasi * $booking->slot->lokasi->tarif;

            $booking->update([
                'status'       => Booking::MENUNGGU_PEMBAYARAN,
                'check_out_at' => $keluar,
                'durasi'       => $durasi,
                'total'        => $total,
            ]);

            return response()->json([
                'lokasi'    => $booking->slot->lokasi->nama,
                'keluar'    => $keluar->format('H:i'),
                'durasi'    => $durasi,
                'total'     => $total,
                'bayar_url' => route('pembayaran', $booking),
            ]);
        });
    }
}
