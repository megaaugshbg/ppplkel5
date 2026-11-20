<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Support\PesananSesi;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

// Riwayat Booking: booking milik Penyewa yang sudah selesai atau dibatalkan.
class RiwayatController extends Controller
{
    /** Status yang masuk riwayat (pesanan aktif ada di halaman Booking Aktif). */
    public const STATUS_RIWAYAT = [Booking::SELESAI, Booking::DIBATALKAN];

    /** Kolom yang dibutuhkan daftar & kartu beranda (data lokasi/slot sudah disnapshot). */
    private const KOLOM_DAFTAR = ['id', 'kode', 'nama_lokasi', 'label_tipe', 'kode_slot', 'status', 'total_biaya', 'waktu_keluar', 'created_at'];

    private const PER_HALAMAN = 10;

    // Daftar riwayat
    public function index(Request $request)
    {
        $status = in_array($request->query('status'), self::STATUS_RIWAYAT, true) ? $request->query('status') : 'semua';

        $riwayat = Booking::milik($request->user()->id)
            ->whereIn('status', $status === 'semua' ? self::STATUS_RIWAYAT : [$status])
            ->orderByDesc('id')
            ->paginate(self::PER_HALAMAN, self::KOLOM_DAFTAR)
            ->withQueryString();

        return view('riwayat', compact('riwayat', 'status'));
    }

    // Detail satu booking (selesai / dibatalkan) milik pengguna ini
    public function detail(Request $request, string $kode)
    {
        // Milik orang lain atau tidak ada: sama-sama 404 (keberadaannya tidak dibocorkan).
        $booking = Booking::milik($request->user()->id)->where('kode', $kode)->first();
        abort_if(! $booking, 404);

        if (in_array($booking->status, Booking::AKTIF, true)) {
            return redirect()->route('check-out')
                ->with('error', "Booking {$booking->kode} masih berjalan. Lihat di Booking Aktif.");
        }

        return view('riwayat-detail', [
            'booking' => $booking,
            'struk'   => PesananSesi::keArray($booking),
            'dipesan' => self::waktuDipesan($booking),
        ]);
    }

    /** 3 riwayat terbaru untuk kartu "Riwayat terakhir" di beranda (kunci: lokasi, tipe, waktu, status, url). */
    public static function terbaru(int $userId, int $jumlah = 3): Collection
    {
        return Booking::milik($userId)
            ->whereIn('status', self::STATUS_RIWAYAT)
            ->orderByDesc('id')
            ->limit($jumlah)
            ->get(self::KOLOM_DAFTAR)
            ->map(fn (Booking $b) => [
                'lokasi' => $b->nama_lokasi,
                'tipe'   => $b->label_tipe,
                'waktu'  => self::tanggal($b) . ' WIB',
                'status' => Booking::LABEL[$b->status] ?? $b->status,
                'status_kode' => $b->status, // selesai | dibatalkan (untuk ikon badge)
                'url'    => route('riwayat.detail', $b->kode),
            ]);
    }

    /** Tanggal item riwayat (WIB, d-m-Y H:i): selesai = waktu keluar, dibatalkan = waktu dipesan. */
    public static function tanggal(Booking $booking): string
    {
        return $booking->status === Booking::SELESAI && $booking->getRawOriginal('waktu_keluar')
            ? PesananSesi::keWib($booking->getRawOriginal('waktu_keluar'))
            : self::waktuDipesan($booking);
    }

    /** created_at dikelola Eloquent dalam timezone aplikasi; ubah ke WIB. */
    public static function waktuDipesan(Booking $booking): string
    {
        return $booking->created_at
            ? $booking->created_at->copy()->setTimezone(PesananSesi::ZONA_WAKTU)->format(PesananSesi::FORMAT_WAKTU)
            : '-';
    }
}
