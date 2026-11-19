<?php

namespace Database\Seeders;

use App\Models\SlotParkir;
use Database\Seeders\Concerns\SeedLahanParkirDummy;
use Illuminate\Database\Seeder;

/**
 * ==========================================================================
 *  DATA DUMMY - BUKAN DATA ASLI
 * ==========================================================================
 *  Data awal beranda (daftar mal per kota). Nama mal dan alamat mengacu ke
 *  tempat nyata di Surabaya, TETAPI koordinat hanya perkiraan, dan tarif,
 *  jam operasional, jumlah slot, serta status slot adalah KARANGAN untuk
 *  keperluan demo. Jangan dipakai sebagai informasi resmi.
 *
 *  Aman dijalankan ulang:
 *  - Lokasi: updateOrCreate berdasarkan (nama, kota), tidak membuat duplikat.
 *    Kolom `buka` (penutupan manual) tidak pernah ditimpa.
 *  - Slot: hanya DITAMBAH sampai jumlah target per tipe. Slot yang sudah ada
 *    tidak pernah dihapus dan statusnya tidak diubah.
 *  - Lantai: semua slot mal dibagi ulang ke lantai (maks. SLOT_PER_LANTAI per
 *    lantai; Woman, Priority, EV di lantai bawah dekat pintu). Yang diubah
 *    HANYA kolom `lantai`. Hasilnya selalu sama, jadi aman diulang.
 *
 *  Jalankan: php artisan db:seed --class=LahanParkirSurabayaDummySeeder
 * ==========================================================================
 */
class LahanParkirSurabayaDummySeeder extends Seeder
{
    use SeedLahanParkirDummy; // logika slot & lantai bersama, lihat Concerns/SeedLahanParkirDummy

    public function run(): void
    {
        $this->seedLokasiDummy($this->data());
    }

    /**
     * DATA DUMMY. Koordinat perkiraan; tarif, jam, dan jumlah slot karangan.
     * 'slot' = target jumlah slot per tipe.
     */
    private function data(): array
    {
        $r = SlotParkir::TIPE_REGULER;
        $w = SlotParkir::TIPE_WANITA;
        $p = SlotParkir::TIPE_PRIORITAS;
        $e = SlotParkir::TIPE_EV;

        return [
            [
                'nama' => 'Parkir Tunjungan Plaza', 'kota' => 'Surabaya', 'area' => 'Surabaya Pusat',
                'alamat' => 'Jl. Basuki Rahmat No. 8-12, Tegalsari, Surabaya',
                'latitude' => -7.2622000, 'longitude' => 112.7385000,
                'jam_buka' => '10:00', 'jam_tutup' => '22:00', 'tarif' => 5000, 'foto' => null,
                'slot' => [$r => 60, $w => 12, $p => 6, $e => 8],
            ],
            [
                'nama' => 'Parkir Mall Galaxy', 'kota' => 'Surabaya', 'area' => 'Surabaya Timur',
                'alamat' => 'Jl. Dharmahusada Indah Timur No. 35-37, Mulyorejo, Surabaya',
                'latitude' => -7.2754000, 'longitude' => 112.7820000,
                'jam_buka' => '10:00', 'jam_tutup' => '22:00', 'tarif' => 5000, 'foto' => null,
                'slot' => [$r => 40, $w => 10, $p => 4, $e => 6],
            ],
            [
                'nama' => 'Parkir Pakuwon Mall', 'kota' => 'Surabaya', 'area' => 'Surabaya Barat',
                'alamat' => 'Jl. Puncak Indah Lontar No. 2, Sambikerep, Surabaya',
                'latitude' => -7.2890000, 'longitude' => 112.6760000,
                'jam_buka' => '10:00', 'jam_tutup' => '22:00', 'tarif' => 6000, 'foto' => null,
                'slot' => [$r => 60, $w => 12, $p => 6, $e => 10],
            ],
            [
                'nama' => 'Parkir Grand City', 'kota' => 'Surabaya', 'area' => 'Surabaya Pusat',
                'alamat' => 'Jl. Walikota Mustajab No. 1, Genteng, Surabaya',
                'latitude' => -7.2620000, 'longitude' => 112.7496000,
                'jam_buka' => '10:00', 'jam_tutup' => '22:00', 'tarif' => 5000, 'foto' => null,
                'slot' => [$r => 45, $w => 8, $p => 4, $e => 4],
            ],
            [
                'nama' => 'Parkir Ciputra World', 'kota' => 'Surabaya', 'area' => 'Surabaya Selatan',
                'alamat' => 'Jl. Mayjend Sungkono No. 89, Dukuh Pakis, Surabaya',
                'latitude' => -7.2927000, 'longitude' => 112.7193000,
                'jam_buka' => '10:00', 'jam_tutup' => '22:00', 'tarif' => 6000, 'foto' => null,
                'slot' => [$r => 50, $w => 10, $p => 4, $e => 8],
            ],
            [
                // Tanpa slot EV: contoh untuk scope tersediaTipe('ev').
                'nama' => 'Parkir Royal Plaza', 'kota' => 'Surabaya', 'area' => 'Surabaya Selatan',
                'alamat' => 'Jl. Ahmad Yani No. 16-18, Wonokromo, Surabaya',
                'latitude' => -7.3089000, 'longitude' => 112.7352000,
                'jam_buka' => '10:00', 'jam_tutup' => '21:00', 'tarif' => 4000, 'foto' => null,
                'slot' => [$r => 40, $w => 8, $p => 4],
            ],
            [
                'nama' => 'Parkir City of Tomorrow', 'kota' => 'Surabaya', 'area' => 'Surabaya Selatan',
                'alamat' => 'Jl. Ahmad Yani No. 288, Gayungan, Surabaya',
                'latitude' => -7.3425000, 'longitude' => 112.7295000,
                'jam_buka' => '10:00', 'jam_tutup' => '22:00', 'tarif' => 4000, 'foto' => null,
                'slot' => [$r => 35, $w => 6, $p => 3, $e => 2],
            ],
            [
                'nama' => 'Parkir East Coast Center', 'kota' => 'Surabaya', 'area' => 'Surabaya Timur',
                'alamat' => 'Jl. Kejawan Putih Mutiara No. 17, Mulyorejo, Surabaya',
                'latitude' => -7.2769000, 'longitude' => 112.8048000,
                'jam_buka' => '10:00', 'jam_tutup' => '21:30', 'tarif' => 5000, 'foto' => null,
                'slot' => [$r => 30, $w => 6, $p => 3, $e => 4],
            ],
        ];
    }
}
