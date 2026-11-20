<?php

namespace App\Support;

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

    public static function ambil(): array
    {
        $pesanan = session(self::KUNCI);

        // Belum ada pesanan dari halaman Pesan Slot: pakai data contoh
        // supaya halaman Pembayaran & Check In tetap bisa dicoba.
        if (! $pesanan) {
            $pesanan = self::contoh();
            self::simpan($pesanan);
        }

        return $pesanan;
    }

    public static function simpan(array $pesanan): void
    {
        session([self::KUNCI => $pesanan]);
    }

    /** Ada pesanan aktif di session? Berbeda dengan ambil(), TIDAK membuat data contoh. */
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

    private static function contoh(): array
    {
        return [
            'kode'         => 'MKR-001',
            'lokasi'       => 'Parkir Tunjungan Plaza',
            'alamat'       => 'Jl. Basuki Rahmat No. 8-12, Tegalsari, Surabaya',
            'tipe'         => 'Normal Parking',
            'slot'         => 'R-05',
            'konektor'     => null,
            'daya'         => null,
            'tarif'        => 5000,
            'tarif_ev_kwh' => null,
            'status'       => self::TERPESAN,
            'contoh'       => true,
        ];
    }
}
