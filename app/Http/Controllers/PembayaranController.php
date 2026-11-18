<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PembayaranController extends Controller
{
    /** /pembayaran tanpa kode: arahkan ke tagihan terbaru yang belum dibayar. */
    public function terbaru(Request $request)
    {
        $booking = Booking::where('user_id', $request->user()->id)
            ->where('status', Booking::MENUNGGU_PEMBAYARAN)
            ->latest('id')
            ->first();

        return $booking
            ? redirect()->route('pembayaran', $booking)
            : redirect('/check-out')->with('error', 'Tidak ada tagihan yang menunggu pembayaran.');
    }

    public function index(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        if (! in_array($booking->status, [Booking::MENUNGGU_PEMBAYARAN, Booking::LUNAS], true)) {
            return redirect('/check-out')->with('error', 'Selesaikan check-out terlebih dahulu sebelum membayar.');
        }

        $booking->load('slot.lokasi', 'pembayaran');
        $pembayaran = $booking->pembayaran;

        $data = [
            'kode'    => $booking->kode,
            'lokasi'  => $booking->slot->lokasi->nama,
            'slot'    => $booking->slot->kode,
            'tanggal' => $booking->check_in_at->locale('id')->translatedFormat('j F Y'),
            'waktu'   => $booking->check_in_at->format('H:i') . ' - ' . $booking->check_out_at->format('H:i'),
            'durasi'  => $booking->durasi,
            'tarif'   => $booking->slot->lokasi->tarif,
            'status'  => $booking->status === Booking::LUNAS ? 'Sudah dibayar' : 'Belum dibayar',
        ];

        return view('pembayaran', [
            'booking'    => $data,
            'total'      => $booking->total,
            'metode'     => Pembayaran::METODE,
            'pembayaran' => $pembayaran,
        ]);
    }

    /** Catat pembayaran. Dipanggil lewat fetch dari halaman Pembayaran. */
    public function bayar(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        $metode = $request->input('metode');
        if (! in_array($metode, Pembayaran::METODE, true)) {
            return response()->json(['pesan' => 'Pilih metode pembayaran terlebih dahulu.'], 422);
        }

        return DB::transaction(function () use ($booking, $metode) {
            $booking = Booking::lockForUpdate()->find($booking->id);

            if ($booking->status !== Booking::MENUNGGU_PEMBAYARAN) {
                return response()->json(['pesan' => 'Tagihan ini tidak dapat dibayar (sudah dibayar atau belum check-out).'], 422);
            }

            Pembayaran::create([
                'booking_id' => $booking->id,
                'metode'     => $metode,
                'jumlah'     => $booking->total,
                'status'     => 'berhasil',
                'dibayar_at' => now(),
            ]);
            $booking->update(['status' => Booking::LUNAS]);

            return response()->json(['metode' => $metode, 'total' => $booking->total]);
        });
    }
}
