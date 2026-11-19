<?php

namespace Database\Seeders;

use App\Models\SlotParkir;
use Database\Seeders\Concerns\SeedLahanParkirDummy;
use Illuminate\Database\Seeder;

/**
 * ==========================================================================
 *  DATA DUMMY - BUKAN DATA ASLI
 * ==========================================================================
 *  Mal di kota selain Surabaya (Jakarta, Bogor, Bandung, Yogyakarta, Malang)
 *  agar pemilih kota di beranda punya pilihan. Nama mal mengacu ke tempat
 *  nyata, TETAPI alamat dan koordinat hanya perkiraan, dan tarif, jam
 *  operasional, jumlah slot, serta status slot adalah KARANGAN untuk demo.
 *  Jangan dipakai sebagai informasi resmi.
 *
 *  Sengaja untuk pengujian kartu tipe "Tidak tersedia":
 *  - Parkir Botani Square   : tanpa slot EV
 *  - Parkir Malioboro Mall  : tanpa slot Priority
 *
 *  Aman dijalankan ulang (lihat Concerns/SeedLahanParkirDummy): updateOrCreate
 *  per (nama, kota), slot hanya ditambah sampai target, status tidak diubah,
 *  lantai dibagi maks. 12 slot per lantai per mal.
 *
 *  Jalankan: php artisan db:seed --class=LahanParkirKotaLainDummySeeder
 * ==========================================================================
 */
class LahanParkirKotaLainDummySeeder extends Seeder
{
    use SeedLahanParkirDummy;

    public function run(): void
    {
        $this->seedLokasiDummy($this->data());
    }

    /**
     * DATA DUMMY. Koordinat perkiraan; tarif, jam, dan jumlah slot karangan.
     * 'slot' = target jumlah slot per tipe. Kolom `buka` sengaja tidak diisi:
     * default tabel = true untuk lokasi baru, dan tidak menimpa tutup manual.
     */
    private function data(): array
    {
        $r = SlotParkir::TIPE_REGULER;
        $w = SlotParkir::TIPE_WANITA;
        $p = SlotParkir::TIPE_PRIORITAS;
        $e = SlotParkir::TIPE_EV;

        return [
            // ------------------------------------------------------------ Jakarta
            [
                'nama' => 'Parkir Senayan City', 'kota' => 'Jakarta', 'area' => 'Jakarta Pusat',
                'alamat' => 'Jl. Asia Afrika Lot 19, Gelora, Tanah Abang, Jakarta',
                'latitude' => -6.2275000, 'longitude' => 106.7973000,
                'jam_buka' => '10:00', 'jam_tutup' => '22:00', 'tarif' => 7000, 'foto' => null,
                'slot' => [$r => 55, $w => 12, $p => 6, $e => 10],
            ],
            [
                'nama' => 'Parkir Lippo Mall Puri', 'kota' => 'Jakarta', 'area' => 'Jakarta Barat',
                'alamat' => 'Jl. Puri Indah Raya Blok U1, Kembangan, Jakarta',
                'latitude' => -6.1878000, 'longitude' => 106.7393000,
                'jam_buka' => '10:00', 'jam_tutup' => '22:00', 'tarif' => 5000, 'foto' => null,
                'slot' => [$r => 45, $w => 10, $p => 4, $e => 6],
            ],
            [
                'nama' => 'Parkir Grand Indonesia', 'kota' => 'Jakarta', 'area' => 'Jakarta Pusat',
                'alamat' => 'Jl. M.H. Thamrin No. 1, Menteng, Jakarta',
                'latitude' => -6.1951000, 'longitude' => 106.8203000,
                'jam_buka' => '10:00', 'jam_tutup' => '23:00', 'tarif' => 8000, 'foto' => null,
                'slot' => [$r => 60, $w => 12, $p => 6, $e => 12],
            ],

            // -------------------------------------------------------------- Bogor
            [
                'nama' => 'Parkir AEON Mall Sentul City', 'kota' => 'Bogor', 'area' => 'Sentul',
                'alamat' => 'Jl. M.H. Thamrin Kav. 9, Sentul City, Babakan Madang, Bogor',
                'latitude' => -6.5596000, 'longitude' => 106.8616000,
                'jam_buka' => '10:00', 'jam_tutup' => '22:00', 'tarif' => 5000, 'foto' => null,
                'slot' => [$r => 40, $w => 8, $p => 4, $e => 6],
            ],
            [
                // Tanpa slot EV: contoh kartu tipe "Tidak tersedia".
                'nama' => 'Parkir Botani Square', 'kota' => 'Bogor', 'area' => 'Bogor Tengah',
                'alamat' => 'Jl. Raya Pajajaran No. 69-71, Tegallega, Bogor',
                'latitude' => -6.6013000, 'longitude' => 106.8066000,
                'jam_buka' => '10:00', 'jam_tutup' => '21:30', 'tarif' => 4000, 'foto' => null,
                'slot' => [$r => 30, $w => 6, $p => 3],
            ],

            // ------------------------------------------------------------ Bandung
            [
                'nama' => 'Parkir Paris Van Java', 'kota' => 'Bandung', 'area' => 'Bandung Utara',
                'alamat' => 'Jl. Sukajadi No. 131-139, Cipedes, Sukajadi, Bandung',
                'latitude' => -6.8888000, 'longitude' => 107.5961000,
                'jam_buka' => '10:00', 'jam_tutup' => '22:00', 'tarif' => 5000, 'foto' => null,
                'slot' => [$r => 40, $w => 8, $p => 4, $e => 4],
            ],
            [
                'nama' => 'Parkir Trans Studio Mall', 'kota' => 'Bandung', 'area' => 'Bandung Timur',
                'alamat' => 'Jl. Gatot Subroto No. 289, Cibangkong, Batununggal, Bandung',
                'latitude' => -6.9253000, 'longitude' => 107.6366000,
                'jam_buka' => '10:00', 'jam_tutup' => '22:00', 'tarif' => 6000, 'foto' => null,
                'slot' => [$r => 50, $w => 10, $p => 5, $e => 8],
            ],

            // --------------------------------------------------------- Yogyakarta
            [
                // Tanpa slot Priority: contoh kartu tipe "Tidak tersedia".
                'nama' => 'Parkir Malioboro Mall', 'kota' => 'Yogyakarta', 'area' => 'Danurejan',
                'alamat' => 'Jl. Malioboro No. 52-58, Suryatmajan, Danurejan, Yogyakarta',
                'latitude' => -7.7925000, 'longitude' => 110.3664000,
                'jam_buka' => '09:30', 'jam_tutup' => '21:30', 'tarif' => 3000, 'foto' => null,
                'slot' => [$r => 25, $w => 6, $e => 2],
            ],
            [
                'nama' => 'Parkir Jogja City Mall', 'kota' => 'Yogyakarta', 'area' => 'Sleman',
                'alamat' => 'Jl. Magelang KM 6 No. 18, Sinduadi, Mlati, Sleman',
                'latitude' => -7.7533000, 'longitude' => 110.3613000,
                'jam_buka' => '10:00', 'jam_tutup' => '22:00', 'tarif' => 4000, 'foto' => null,
                'slot' => [$r => 35, $w => 8, $p => 4, $e => 4],
            ],

            // ------------------------------------------------------------- Malang
            [
                'nama' => 'Parkir Malang Town Square', 'kota' => 'Malang', 'area' => 'Klojen',
                'alamat' => 'Jl. Veteran No. 2, Penanggungan, Klojen, Malang',
                'latitude' => -7.9569000, 'longitude' => 112.6187000,
                'jam_buka' => '10:00', 'jam_tutup' => '21:00', 'tarif' => 3000, 'foto' => null,
                'slot' => [$r => 30, $w => 6, $p => 3, $e => 2],
            ],
            [
                'nama' => 'Parkir Mall Olympic Garden', 'kota' => 'Malang', 'area' => 'Klojen',
                'alamat' => 'Jl. Kawi No. 24, Kauman, Klojen, Malang',
                'latitude' => -7.9774000, 'longitude' => 112.6253000,
                'jam_buka' => '09:00', 'jam_tutup' => '21:00', 'tarif' => 3000, 'foto' => null,
                'slot' => [$r => 28, $w => 6, $p => 3, $e => 3],
            ],
        ];
    }
}
