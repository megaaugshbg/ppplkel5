<?php

namespace App\Support;

class ParkirData
{
    public static function tipe(): array
    {
        return [
            'reguler' => 'Parkir Reguler',
            'wanita' => 'Woman Parking',
        ];
    }

    // slot yang "diserobot" pengguna lain saat dipesan (simulasi alternate flow)
    public static function rebutan(): array
    {
        return ['1-A06'];
    }

    public static function lokasi(): array
    {
        $daftar = [
            1 => ['id' => 1, 'nama' => 'Parkir Mall Galaxy', 'alamat' => 'Jl. Dharmahusada Indah, Surabaya', 'tipe' => ['reguler', 'wanita'], 'tarif' => 5000, 'jarak' => 0.8, 'buka' => true],
            2 => ['id' => 2, 'nama' => 'Parkir Kampus ITS', 'alamat' => 'Jl. Raya ITS, Sukolilo, Surabaya', 'tipe' => ['reguler'], 'tarif' => 3000, 'jarak' => 1.5, 'buka' => true],
            3 => ['id' => 3, 'nama' => 'Parkir Pakuwon Mall', 'alamat' => 'Jl. Mayjend Yono Soewoyo, Surabaya', 'tipe' => ['reguler', 'wanita'], 'tarif' => 6000, 'jarak' => 4.2, 'buka' => false],
        ];

        // hitung slot tersedia per tipe
        foreach ($daftar as $id => $l) {
            foreach ($l['tipe'] as $t) {
                $daftar[$id]['slot_tipe'][$t] = count(array_filter(
                    self::slots($id, $t),
                    fn ($s) => ! $s['terisi']
                ));
            }
        }

        return $daftar;
    }

    public static function slots(int $lokasiId, ?string $tipe = null): array
    {
        $terisiBaru = session('slot_terisi', []);
        $hasil = [];

        foreach (self::semuaSlot()[$lokasiId] ?? [] as $s) {
            if ($tipe !== null && $s['tipe'] !== $tipe) {
                continue;
            }
            if (in_array($lokasiId . '-' . $s['kode'], $terisiBaru, true)) {
                $s['terisi'] = true;
            }
            $hasil[] = $s;
        }

        return $hasil;
    }

    private static function semuaSlot(): array
    {
        return [
            1 => array_merge(
                self::deret('A', 1, 4, 1, 'wanita', ['A01', 'A03']),
                self::deret('A', 5, 10, 1, 'reguler', ['A07']),
                self::deret('B', 1, 10, 2, 'reguler', ['B03', 'B04', 'B09'])
            ),
            2 => self::deret('C', 1, 12, 1, 'reguler', ['C01', 'C02', 'C03', 'C08']),
            3 => array_merge(
                self::deret('D', 1, 4, 1, 'wanita'),
                self::deret('D', 5, 10, 1, 'reguler')
            ),
        ];
    }

    private static function deret(string $prefix, int $dari, int $sampai, int $lantai, string $tipe, array $terisi = []): array
    {
        $hasil = [];
        for ($i = $dari; $i <= $sampai; $i++) {
            $kode = $prefix . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $hasil[] = [
                'kode' => $kode,
                'lantai' => $lantai,
                'tipe' => $tipe,
                'terisi' => in_array($kode, $terisi, true),
            ];
        }

        return $hasil;
    }
}
