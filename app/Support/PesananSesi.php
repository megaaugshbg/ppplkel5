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
