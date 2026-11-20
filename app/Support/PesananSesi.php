<?php

namespace App\Support;

use Illuminate\Http\RedirectResponse;

/**
 * Pesanan (booking) terakhir milik Penyewa.
 * Tabel booking belum ada, jadi pesanan disimpan di session 'pesanan_terakhir'
 * (diisi SlotController saat Pesan Slot). Dipakai bersama oleh Pembayaran dan Check In.
 */
class PesananSesi
{
    public const KUNCI = 'pesanan_terakhir';

    // Status pesanan sesuai urutan alur
    public const TERPESAN = 'Terpesan';
    public const SUDAH_BAYAR = 'Sudah dibayar';
    public const SUDAH_CHECKIN = 'Sudah check in';
    public const SELESAI = 'Selesai'; // setelah check-out (disimpan di struk, bukan di pesanan aktif)

    /** Pesanan aktif di session, atau null kalau belum ada pesanan. */
    public static function ambil(): ?array
    {
        return session(self::KUNCI);
    }

    /** Dipakai saat halaman dibuka tanpa pesanan aktif. */
    public static function arahkanKeBeranda(): RedirectResponse
    {
        return redirect()->route('home')
            ->with('pesan', 'Belum ada pesanan aktif. Silakan pesan slot parkir terlebih dahulu.');
    }

    public static function simpan(array $pesanan): void
    {
        session([self::KUNCI => $pesanan]);
    }

    /** Ada pesanan aktif di session? */
    public static function ada(): bool
    {
        return (bool) session(self::KUNCI);
    }

    /** Hapus pesanan aktif (setelah check-out) supaya Penyewa bisa memesan lagi. */
    public static function kosongkan(): void
    {
        session()->forget(self::KUNCI);
    }

    public static function sudahBayar(array $pesanan): bool
    {
        return in_array($pesanan['status'], [self::SUDAH_BAYAR, self::SUDAH_CHECKIN], true);
    }

    public static function sudahCheckIn(array $pesanan): bool
    {
        return $pesanan['status'] === self::SUDAH_CHECKIN;
    }
}
