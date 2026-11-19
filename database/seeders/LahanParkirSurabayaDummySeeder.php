<?php

namespace Database\Seeders;

use App\Models\LokasiParkir;
use App\Models\SlotParkir;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
    /** Awalan kode slot per tipe, contoh: R-01, W-01, P-01, E-01. */
    private const AWALAN = [
        SlotParkir::TIPE_REGULER   => 'R',
        SlotParkir::TIPE_WANITA    => 'W',
        SlotParkir::TIPE_PRIORITAS => 'P',
        SlotParkir::TIPE_EV        => 'E',
    ];

    /** Dummy: setiap slot baru ke-N per tipe dibuat 'terisi' agar ketersediaan bervariasi. */
    private const SETIAP_KE_TERISI = 4;

    /** Maksimal slot per lantai per mal. */
    private const SLOT_PER_LANTAI = 12;

    /** Urutan tipe dari lantai bawah ke atas: slot khusus dekat pintu masuk. */
    private const URUTAN_LANTAI = [
        SlotParkir::TIPE_WANITA,
        SlotParkir::TIPE_PRIORITAS,
        SlotParkir::TIPE_EV,
        SlotParkir::TIPE_REGULER,
    ];

    public function run(): void
    {
        $adaKolomLantai = Schema::hasColumn('slot_parkir', 'lantai');
        if (! $adaKolomLantai) {
            $this->command?->warn('Kolom slot_parkir.lantai belum ada: jalankan "php artisan migrate" dulu. Pembagian lantai dilewati.');
        }

        foreach ($this->data() as $row) {
            DB::transaction(function () use ($row, $adaKolomLantai) {
                $target = $row['slot'];
                unset($row['slot']);

                $lokasi = LokasiParkir::updateOrCreate(
                    ['nama' => $row['nama'], 'kota' => $row['kota']],
                    $row,
                );

                $this->isiSlotSampaiTarget($lokasi, $target);

                if ($adaKolomLantai) {
                    $this->bagiLantai($lokasi);
                }
            });
        }
    }

    /** Tambah slot per tipe sampai jumlah target; tidak pernah menghapus. */
    private function isiSlotSampaiTarget(LokasiParkir $lokasi, array $target): void
    {
        $kodeTerpakai = $lokasi->slots()->pluck('kode')->flip()->all();
        $jumlahPerTipe = $lokasi->slots()
            ->selectRaw('tipe, COUNT(*) as jumlah')
            ->groupBy('tipe')
            ->pluck('jumlah', 'tipe');

        $baru = [];
        $sekarang = now();

        foreach ($target as $tipe => $jumlahTarget) {
            $kurang = $jumlahTarget - (int) ($jumlahPerTipe[$tipe] ?? 0);

            for ($i = 1; $kurang > 0; $i++) {
                $kode = sprintf('%s-%02d', self::AWALAN[$tipe], $i);
                if (isset($kodeTerpakai[$kode])) {
                    continue;
                }

                $kodeTerpakai[$kode] = true;
                $baru[] = [
                    'lokasi_parkir_id' => $lokasi->id,
                    'kode'             => $kode,
                    'tipe'             => $tipe,
                    'status'           => $i % self::SETIAP_KE_TERISI === 0
                        ? SlotParkir::STATUS_TERISI
                        : SlotParkir::STATUS_TERSEDIA,
                    'created_at'       => $sekarang,
                    'updated_at'       => $sekarang,
                ];
                $kurang--;
            }
        }

        foreach (array_chunk($baru, 200) as $potongan) {
            SlotParkir::insert($potongan);
        }
    }

    /**
     * Bagi semua slot mal ke lantai: urut tipe (URUTAN_LANTAI) lalu nomor kode,
     * tiap SLOT_PER_LANTAI slot naik satu lantai. Hanya kolom `lantai` yang
     * di-update, dan hanya untuk slot yang lantainya berubah.
     */
    private function bagiLantai(LokasiParkir $lokasi): void
    {
        $urutTipe = array_flip(self::URUTAN_LANTAI);

        $slot = $lokasi->slots()->get(['id', 'kode', 'tipe', 'lantai'])
            ->sortBy(fn (SlotParkir $s) => sprintf(
                '%d-%06d-%s',
                $urutTipe[$s->tipe] ?? 99,
                (int) preg_replace('/\D/', '', $s->kode),
                $s->kode,
            ))
            ->values();

        $pindah = []; // lantai => [id, ...]
        foreach ($slot as $i => $s) {
            $lantai = intdiv($i, self::SLOT_PER_LANTAI) + 1;
            if ($s->lantai !== $lantai) {
                $pindah[$lantai][] = $s->id;
            }
        }

        foreach ($pindah as $lantai => $ids) {
            SlotParkir::whereIn('id', $ids)->update(['lantai' => $lantai]);
        }
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
