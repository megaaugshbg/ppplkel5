<?php

namespace Database\Seeders;

use App\Models\LokasiParkir;
use App\Models\SlotParkir;
use Illuminate\Database\Seeder;

class LokasiParkirSeeder extends Seeder
{
    /**
     * Data contoh yang sebelumnya hardcode di ParkirController.
     * Format slot: [awalan kode, tipe, jumlah].
     */
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Parkir Mall Galaxy', 'alamat' => 'Jl. Dharmahusada Indah, Surabaya',
                'tarif' => 5000, 'jarak' => 0.8, 'buka' => true,
                'slot' => [['A', SlotParkir::TIPE_REGULER, 18], ['W', SlotParkir::TIPE_WANITA, 6]],
            ],
            [
                'nama' => 'Parkir Kampus ITS', 'alamat' => 'Jl. Raya ITS, Sukolilo, Surabaya',
                'tarif' => 3000, 'jarak' => 1.5, 'buka' => true,
                'slot' => [['B', SlotParkir::TIPE_REGULER, 40]],
            ],
            [
                'nama' => 'Parkir Pakuwon Mall', 'alamat' => 'Jl. Mayjend Yono Soewoyo, Surabaya',
                'tarif' => 6000, 'jarak' => 4.2, 'buka' => false,
                'slot' => [['C', SlotParkir::TIPE_REGULER, 8], ['W', SlotParkir::TIPE_WANITA, 4]],
            ],
        ];

        foreach ($data as $row) {
            $lokasi = LokasiParkir::updateOrCreate(
                ['nama' => $row['nama']],
                collect($row)->except('slot')->all(),
            );

            foreach ($row['slot'] as [$awalan, $tipe, $jumlah]) {
                for ($i = 1; $i <= $jumlah; $i++) {
                    SlotParkir::firstOrCreate(
                        ['lokasi_parkir_id' => $lokasi->id, 'kode' => sprintf('%s-%02d', $awalan, $i)],
                        ['tipe' => $tipe],
                    );
                }
            }
        }
    }
}
