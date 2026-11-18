<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\SlotParkir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    /** Insert form: menyimpan booking baru untuk slot yang dipilih. */
    public function store(Request $request)
    {
        // rapikan plat nomor sebelum divalidasi: huruf besar, spasi tunggal
        $request->merge([
            'plat_nomor' => strtoupper(preg_replace('/\s+/', ' ', trim((string) $request->input('plat_nomor')))),
        ]);

        $data = $request->validate([
            'slot_parkir_id' => ['required', 'exists:slot_parkir,id'],
            'plat_nomor'     => ['required', 'regex:/^[A-Z]{1,2} ?[0-9]{1,4} ?[A-Z]{0,3}$/'],
        ], [
            'slot_parkir_id.required' => 'Pilih slot parkir terlebih dahulu.',
            'slot_parkir_id.exists'   => 'Slot parkir tidak ditemukan.',
            'plat_nomor.required'     => 'Plat nomor wajib diisi.',
            'plat_nomor.regex'        => 'Format plat nomor tidak valid. Contoh: L 1234 AB.',
        ]);

        $user = $request->user();

        if (Booking::where('user_id', $user->id)->aktif()->exists()) {
            return redirect('/check-out')->with('error', 'Anda masih punya booking aktif. Selesaikan check-out terlebih dahulu.');
        }

        $booking = DB::transaction(function () use ($data, $user) {
            // kunci baris slot supaya dua orang tidak bisa memesan slot yang sama bersamaan
            $slot = SlotParkir::with('lokasi')->lockForUpdate()->findOrFail($data['slot_parkir_id']);

            if (! $slot->lokasi->buka) {
                throw ValidationException::withMessages(['slot_parkir_id' => 'Lokasi parkir sedang tutup.']);
            }
            if ($slot->bookings()->aktif()->exists()) {
                throw ValidationException::withMessages(['slot_parkir_id' => 'Slot ' . $slot->kode . ' baru saja dipesan orang lain. Silakan pilih slot lain.']);
            }

            $booking = Booking::create([
                'kode'           => Str::random(12), // sementara, diganti setelah id diketahui
                'user_id'        => $user->id,
                'slot_parkir_id' => $slot->id,
                'plat_nomor'     => $data['plat_nomor'],
                'status'         => Booking::DIPESAN,
            ]);
            $booking->update(['kode' => sprintf('MKR-%03d', $booking->id)]);

            return $booking;
        });

        return redirect('/check-out')->with('sukses', 'Booking berhasil. Kode booking Anda: ' . $booking->kode . '. Lakukan check-in saat tiba di lokasi.');
    }

    public function checkIn(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);

        if ($booking->status !== Booking::DIPESAN) {
            return redirect('/check-out')->with('error', 'Booking ini tidak bisa di-check-in.');
        }

        $booking->update(['status' => Booking::PARKIR, 'check_in_at' => now()]);

        return redirect('/check-out')->with('sukses', 'Check-in berhasil. Selamat parkir!');
    }
}
