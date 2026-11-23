<?php

namespace App\Support;

use App\Models\Booking;
use App\Models\SlotParkir;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

/**
 * Pesanan (booking) milik Penyewa yang login.
 *
 * Adapter ke tabel `booking` (App\Models\Booking). Bentuk array pesanan SAMA
 * dengan versi session sebelumnya, supaya Pembayaran, Check In, Detail Booking,
 * Check-out, dan view-nya tetap bekerja tanpa diubah:
 *   kode, lokasi, alamat, tipe, slot, konektor, daya, tarif, tarif_ev_kwh, status,
 *   durasi, total, metode, no_transaksi, waktu_bayar, waktu_checkin,
 *   waktu_keluar, durasi_aktual, melebihi_jam, total_biaya.
 * Waktu di array berformat 'd-m-Y H:i' WIB; di database disimpan UTC.
 */
class PesananSesi
{
    /** Kunci session versi lama. Tidak dibaca lagi; dihapus diam-diam bila masih ada. */
    public const KUNCI = 'pesanan_terakhir';

    /** Penanda di session: pesanan pengguna ini baru saja dibatalkan karena lewat batas bayar. */
    public const KUNCI_BATAS_HABIS = 'batas_bayar_habis';

    // Label status (nilai 'status' di array pesanan)
    public const TERPESAN = 'Terpesan';
    public const SUDAH_BAYAR = 'Sudah dibayar';
    public const SUDAH_CHECKIN = 'Sudah check in';
    public const SELESAI = 'Selesai';
    public const DIBATALKAN = 'Dibatalkan';

    public const ZONA_WAKTU = 'Asia/Jakarta';
    public const FORMAT_WAKTU = 'd-m-Y H:i';

    /** kunci array => kolom tabel booking */
    private const KOLOM = [
        'kode'          => 'kode',
        'lokasi'        => 'nama_lokasi',
        'alamat'        => 'alamat_lokasi',
        'tipe'          => 'label_tipe',
        'slot'          => 'kode_slot',
        'konektor'      => 'konektor',
        'daya'          => 'daya_kw',
        'tarif'         => 'tarif',
        'tarif_ev_kwh'  => 'tarif_ev_kwh',
        'durasi'        => 'durasi',
        'total'         => 'total',
        'metode'        => 'metode',
        'no_transaksi'  => 'no_transaksi',
        'waktu_bayar'   => 'waktu_bayar',
        'waktu_checkin' => 'waktu_checkin',
        'waktu_keluar'  => 'waktu_keluar',
        'durasi_aktual' => 'durasi_aktual',
        'melebihi_jam'  => 'melebihi_jam',
        'total_biaya'   => 'total_biaya',
    ];

    private const KOLOM_WAKTU = ['waktu_bayar', 'waktu_checkin', 'waktu_keluar'];

    /** Pesanan aktif (terpesan / sudah dibayar / sudah check in) milik pengguna, atau null. */
    public static function ambil(): ?array
    {
        self::lupakanSesiLama();

        $booking = self::bookingAktif();

        return $booking ? self::keArray($booking) : null;
    }

    /** Dipakai saat halaman dibuka tanpa pesanan aktif. */
    public static function arahkanKeBeranda(): RedirectResponse
    {
        $pesan = session(self::KUNCI_BATAS_HABIS)
            ? session('pesan')
            : 'Belum ada pesanan aktif. Silakan pesan slot parkir terlebih dahulu.';

        return redirect()->route('home')->with('pesan', $pesan);
    }

    /**
     * Batalkan semua pesanan yang belum dibayar dan sudah lewat batas waktu bayar
     * (Booking::BATAS_BAYAR_MENIT), lalu kembalikan slotnya jadi tersedia.
     * Dipanggil di setiap request lewat middleware BatalkanPesananLewatBatas.
     */
    public static function batalkanLewatBatasBayar(): void
    {
        if (! Booking::lewatBatasBayar()->exists()) {
            return;
        }

        $userId = Auth::id();

        $kodeMilikku = DB::transaction(function () use ($userId) {
            $kodeMilikku = null;

            foreach (Booking::lewatBatasBayar()->lockForUpdate()->get() as $booking) {
                $booking->update(['status' => Booking::DIBATALKAN]);
                SlotParkir::whereKey($booking->slot_parkir_id)
                    ->where('status', SlotParkir::STATUS_TERPESAN)
                    ->update(['status' => SlotParkir::STATUS_TERSEDIA]);

                if ($userId && (int) $booking->user_id === (int) $userId) {
                    $kodeMilikku = $booking->kode;
                }
            }

            return $kodeMilikku;
        });

        if ($kodeMilikku) {
            session()->flash(self::KUNCI_BATAS_HABIS, true);
            session()->flash('pesan', "Waktu pembayaran pesanan {$kodeMilikku} sudah habis ("
                . Booking::BATAS_BAYAR_MENIT . ' menit), jadi pesanan dibatalkan otomatis. Silakan pesan ulang.');
        }
    }

    /**
     * Tulis perubahan array pesanan aktif ke baris booking (dicari lewat kode
     * milik pengguna yang login). Bila status berubah menjadi "Sudah check in",
     * slot ikut menjadi terisi di transaksi yang sama.
     */
    public static function simpan(array $pesanan): void
    {
        $userId = Auth::id();

        DB::transaction(function () use ($pesanan, $userId) {
            $booking = Booking::milik((int) $userId)->aktif()
                ->where('kode', $pesanan['kode'] ?? null)
                ->lockForUpdate()
                ->first();

            if (! $booking) {
                throw new RuntimeException('Pesanan aktif tidak ditemukan.');
            }

            $statusLama = $booking->status;

            foreach (self::KOLOM as $kunci => $kolom) {
                if ($kunci === 'kode' || ! array_key_exists($kunci, $pesanan)) {
                    continue;
                }
                $booking->{$kolom} = in_array($kunci, self::KOLOM_WAKTU, true)
                    ? self::keUtc($pesanan[$kunci])
                    : $pesanan[$kunci];
            }

            if (isset($pesanan['status'])) {
                $status = Booking::statusDariLabel($pesanan['status']);
                // simpan() hanya untuk tahap aktif; selesai/dibatalkan punya alur sendiri.
                if (! in_array($status, Booking::AKTIF, true)) {
                    throw new RuntimeException('Status pesanan tidak valid: ' . $pesanan['status']);
                }
                $booking->status = $status;
            }

            $booking->save();

            if ($booking->status === Booking::SUDAH_CHECKIN && $statusLama !== Booking::SUDAH_CHECKIN) {
                SlotParkir::whereKey($booking->slot_parkir_id)->update(['status' => SlotParkir::STATUS_TERISI]);
            }
        });
    }

    /** Ada pesanan aktif milik pengguna? */
    public static function ada(): bool
    {
        return self::bookingAktif() !== null;
    }

    /**
     * Tutup pesanan aktif: HANYA booking berstatus terpesan (belum dibayar) yang
     * dibatalkan, dan slotnya kembali tersedia. Status lain tidak diubah.
     *
     * @return string|null kode booking yang dibatalkan, atau null bila tidak ada
     */
    public static function kosongkan(): ?string
    {
        $userId = Auth::id();
        if (! $userId) {
            return null;
        }

        return DB::transaction(function () use ($userId) {
            $booking = Booking::milik($userId)->where('status', Booking::TERPESAN)->lockForUpdate()->first();
            if (! $booking) {
                return null;
            }

            $booking->update(['status' => Booking::DIBATALKAN]);
            SlotParkir::whereKey($booking->slot_parkir_id)
                ->where('status', SlotParkir::STATUS_TERPESAN)
                ->update(['status' => SlotParkir::STATUS_TERSEDIA]);

            return $booking->kode;
        });
    }

    /** Booking selesai (sudah check-out) terbaru milik pengguna, sebagai array struk. */
    public static function strukTerakhir(): ?array
    {
        $userId = Auth::id();
        if (! $userId) {
            return null;
        }

        // Urut id (bukan waktu_keluar): satu pengguna hanya punya satu pesanan aktif,
        // jadi urutan dibuat = urutan check-out, dan tidak terpengaruh jam server yang bergeser.
        $booking = Booking::milik($userId)->where('status', Booking::SELESAI)
            ->orderByDesc('id')->first();

        return $booking ? self::keArray($booking) : null;
    }

    public static function sudahBayar(array $pesanan): bool
    {
        return in_array($pesanan['status'], [self::SUDAH_BAYAR, self::SUDAH_CHECKIN], true);
    }

    public static function sudahCheckIn(array $pesanan): bool
    {
        return $pesanan['status'] === self::SUDAH_CHECKIN;
    }

    /** Baris booking -> array pesanan (bentuk lama; waktu dalam WIB). */
    public static function keArray(Booking $booking): array
    {
        $pesanan = [];
        foreach (self::KOLOM as $kunci => $kolom) {
            $pesanan[$kunci] = in_array($kunci, self::KOLOM_WAKTU, true)
                ? self::keWib($booking->getRawOriginal($kolom) ?? $booking->getAttribute($kolom))
                : $booking->getAttribute($kolom);
        }
        $pesanan['status'] = Booking::LABEL[$booking->status] ?? $booking->status;

        // Batas bayar, hanya untuk pesanan yang belum dibayar
        if ($booking->status === Booking::TERPESAN && $booking->created_at) {
            $batas = $booking->batasBayar();
            $pesanan['batas_bayar'] = $batas->copy()->setTimezone(self::ZONA_WAKTU)->format('H:i');
            $pesanan['sisa_detik'] = max(0, (int) now()->diffInSeconds($batas, false));
        }

        return $pesanan;
    }

    /** '20-11-2026 06:30' (WIB) -> '2026-11-19 23:30:00' (UTC). */
    public static function keUtc(?string $wib): ?string
    {
        if ($wib === null || $wib === '') {
            return null;
        }

        return Carbon::createFromFormat(self::FORMAT_WAKTU, $wib, self::ZONA_WAKTU)
            ->setTimezone('UTC')
            ->format('Y-m-d H:i:s');
    }

    /** '2026-11-19 23:30:00' (UTC) -> '20-11-2026 06:30' (WIB). */
    public static function keWib(?string $utc): ?string
    {
        if ($utc === null || $utc === '') {
            return null;
        }

        return Carbon::parse($utc, 'UTC')->setTimezone(self::ZONA_WAKTU)->format(self::FORMAT_WAKTU);
    }

    private static function bookingAktif(): ?Booking
    {
        $userId = Auth::id();
        if (! $userId) {
            return null;
        }

        return Booking::milik($userId)->aktif()->orderByDesc('id')->first();
    }

    /** Pesanan versi lama di session diabaikan dengan aman. */
    private static function lupakanSesiLama(): void
    {
        $request = request();
        if ($request->hasSession() && $request->session()->has(self::KUNCI)) {
            $request->session()->forget(self::KUNCI);
        }
    }
}
