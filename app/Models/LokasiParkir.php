<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LokasiParkir extends Model
{
    /** Jam operasional lahan parkir dibaca dalam waktu lokal. */
    public const ZONA_WAKTU = 'Asia/Jakarta';

    /** Gambar dipakai jika kolom foto kosong / file-nya tidak ada (relatif dari public/). */
    public const FOTO_DEFAULT = 'images/mal/default.png';

    protected $table = 'lokasi_parkir';

    protected $fillable = [
        'nama', 'alamat', 'area', 'kota', 'latitude', 'longitude',
        'foto', 'jam_buka', 'jam_tutup', 'tarif', 'tarif_ev_kwh', 'buka',
    ];

    protected function casts(): array
    {
        return [
            'tarif'        => 'integer',
            'tarif_ev_kwh' => 'integer',
            'buka'         => 'boolean',
            'latitude'     => 'float',
            'longitude'    => 'float',
        ];
    }

    public function slots(): HasMany
    {
        return $this->hasMany(SlotParkir::class, 'lokasi_parkir_id');
    }

    // ---------------------------------------------------------------- scope

    /** Lahan parkir di kota tertentu, contoh: LokasiParkir::kota('Surabaya'). */
    public function scopeKota(Builder $query, string $kota): Builder
    {
        return $query->whereRaw('LOWER(kota) = ?', [mb_strtolower(trim($kota))]);
    }

    /** Lahan parkir yang masih punya minimal satu slot kosong dengan tipe tertentu (SlotParkir::TIPE_*). */
    public function scopeTersediaTipe(Builder $query, string $tipe): Builder
    {
        return $query->whereHas('slots', fn (Builder $q) => $q->tipe($tipe)->tersedia());
    }

    /**
     * Tambahkan jumlah slot per tipe, dihitung langsung dari tabel slot_parkir
     * (tidak disimpan sebagai kolom supaya selalu sinkron). "Tersedia" = slot
     * berstatus 'tersedia'. Menghasilkan atribut:
     * slot_reguler_total, slot_reguler_tersedia, slot_wanita_total, ... slot_ev_tersedia.
     */
    public function scopeDenganJumlahSlot(Builder $query): Builder
    {
        $hitung = [];
        foreach (SlotParkir::SEMUA_TIPE as $tipe) {
            $hitung["slots as slot_{$tipe}_total"] = fn (Builder $q) => $q->tipe($tipe);
            $hitung["slots as slot_{$tipe}_tersedia"] = fn (Builder $q) => $q->tipe($tipe)->tersedia();
        }

        return $query->withCount($hitung);
    }

    // ------------------------------------------------------------- accessor

    /**
     * Ringkasan slot per tipe:
     * ['reguler' => ['label' => 'Normal Parking', 'total' => 40, 'tersedia' => 31], ...]
     * Pakai bersama scope denganJumlahSlot() supaya tidak menjalankan query per lokasi.
     */
    protected function ringkasanSlot(): Attribute
    {
        return Attribute::get(function () {
            $hasil = [];
            foreach (SlotParkir::SEMUA_TIPE as $tipe) {
                $total = $this->attributes["slot_{$tipe}_total"]
                    ?? $this->slots()->tipe($tipe)->count();
                $tersedia = $this->attributes["slot_{$tipe}_tersedia"]
                    ?? $this->slots()->tipe($tipe)->tersedia()->count();

                $hasil[$tipe] = [
                    'label'    => SlotParkir::LABEL[$tipe],
                    'total'    => (int) $total,
                    'tersedia' => (int) $tersedia,
                ];
            }

            return $hasil;
        });
    }

    /**
     * true jika lahan parkir sedang buka sekarang (waktu Asia/Jakarta).
     * Saklar pengembangan config('parkir.abaikan_jam_operasional') + APP_ENV=local:
     * jam operasional diabaikan, hanya kolom `buka` (tutup manual) yang diperiksa.
     */
    protected function sedangBuka(): Attribute
    {
        return Attribute::get(fn () => self::abaikanJamOperasional()
            ? (bool) $this->buka
            : $this->bukaPada(now(self::ZONA_WAKTU)));
    }

    /**
     * Keterangan bila sedang tutup: "Ditutup sementara" (kolom buka = false)
     * atau "Buka pukul 10:00" (di luar jam operasional). null bila sedang buka.
     */
    protected function keteranganTutup(): Attribute
    {
        return Attribute::get(function () {
            if ($this->sedang_buka) {
                return null;
            }

            return ! $this->buka || ! $this->jam_buka
                ? 'Ditutup sementara'
                : 'Buka pukul ' . substr($this->jam_buka, 0, 5);
        });
    }

    /** Saklar pengembangan aktif? (hanya di APP_ENV=local) */
    public static function abaikanJamOperasional(): bool
    {
        return config('parkir.abaikan_jam_operasional') && app()->environment('local');
    }

    /** "Buka" atau "Tutup". */
    protected function statusOperasional(): Attribute
    {
        return Attribute::get(fn () => $this->sedang_buka ? 'Buka' : 'Tutup');
    }

    /** Contoh: "10:00 - 22:00", atau null jika jam belum diisi. */
    protected function jamOperasional(): Attribute
    {
        return Attribute::get(fn () => $this->jam_buka && $this->jam_tutup
            ? substr($this->jam_buka, 0, 5) . ' - ' . substr($this->jam_tutup, 0, 5)
            : null);
    }

    /** URL foto lahan parkir, atau gambar default jika belum ada. */
    protected function fotoUrl(): Attribute
    {
        return Attribute::get(fn () => asset(
            $this->foto && is_file(public_path($this->foto)) ? $this->foto : self::FOTO_DEFAULT
        ));
    }

    // ----------------------------------------------------------------- util

    /**
     * Apakah lahan parkir buka pada waktu tertentu.
     * - Kolom `buka` = false berarti ditutup manual (selalu tutup).
     * - Jam kosong: dianggap mengikuti kolom `buka` saja.
     * - jam_buka == jam_tutup: buka 24 jam.
     * - jam_tutup < jam_buka: melewati tengah malam (mis. 22:00 - 02:00).
     */
    public function bukaPada(CarbonInterface $waktu): bool
    {
        if (! $this->buka) {
            return false;
        }

        if (! $this->jam_buka || ! $this->jam_tutup) {
            return true;
        }

        $sekarang = $waktu->copy()->setTimezone(self::ZONA_WAKTU)->format('H:i');
        $buka = substr($this->jam_buka, 0, 5);
        $tutup = substr($this->jam_tutup, 0, 5);

        if ($buka === $tutup) {
            return true;
        }

        return $buka < $tutup
            ? $sekarang >= $buka && $sekarang < $tutup
            : $sekarang >= $buka || $sekarang < $tutup;
    }
}
