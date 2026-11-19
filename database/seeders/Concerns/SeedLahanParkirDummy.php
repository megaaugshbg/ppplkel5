<?php

namespace Database\Seeders\Concerns;

use App\Models\LokasiParkir;
use App\Models\SlotParkir;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Logika bersama seeder lahan parkir DUMMY (Surabaya, kota lain, ...).
 *
 * Aman dijalankan ulang:
 * - Lokasi: updateOrCreate berdasarkan (nama, kota), tidak membuat duplikat.
 *   Kolom `buka` (penutupan manual) tidak pernah ditimpa (jangan taruh di data).
 * - Slot: hanya DITAMBAH sampai jumlah target per tipe. Slot yang sudah ada
 *   tidak pernah dihapus dan statusnya tidak diubah.
 * - Lantai: semua slot mal dibagi ulang ke lantai (maks. SLOT_PER_LANTAI per
 *   lantai; Woman, Priority, EV di lantai bawah dekat pintu). Yang diubah
 *   HANYA kolom `lantai`. Hasilnya selalu sama, jadi aman diulang.
 *
 * Format tiap baris data: kolom lokasi_parkir + 'slot' => [tipe => jumlah target].
 */
trait SeedLahanParkirDummy
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

    /** Simpan semua baris data lokasi beserta slot dan lantainya. */
    private function seedLokasiDummy(array $data): void
    {
        $adaKolomLantai = Schema::hasColumn('slot_parkir', 'lantai');
        if (! $adaKolomLantai) {
            $this->command?->warn('Kolom slot_parkir.lantai belum ada: jalankan "php artisan migrate" dulu. Pembagian lantai dilewati.');
        }

        foreach ($data as $row) {
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
}
