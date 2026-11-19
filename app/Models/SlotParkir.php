<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlotParkir extends Model
{
    // Nilai kolom `tipe` (maks. 10 karakter).
    public const TIPE_REGULER   = 'reguler';
    public const TIPE_WANITA    = 'wanita';
    public const TIPE_PRIORITAS = 'prioritas';
    public const TIPE_EV        = 'ev';

    // Tipe yang SUDAH bisa dipesan lewat alur pemesanan (untuk validasi pemesanan).
    // Prioritas belum dimasukkan karena butuh verifikasi kartu disabilitas; EV menyusul.
    public const TIPE = [self::TIPE_REGULER, self::TIPE_WANITA];

    // Semua tipe slot yang ada di data (untuk hitungan & tampilan beranda).
    public const SEMUA_TIPE = [self::TIPE_REGULER, self::TIPE_WANITA, self::TIPE_PRIORITAS, self::TIPE_EV];

    // Nama tipe yang ditampilkan ke pengguna (sesuai CDR).
    public const LABEL = [
        self::TIPE_REGULER   => 'Normal Parking',
        self::TIPE_WANITA    => 'Woman Parking',
        self::TIPE_PRIORITAS => 'Priority Parking',
        self::TIPE_EV        => 'EV Charging Parking',
    ];

    // Nilai kolom `status`.
    public const STATUS_TERSEDIA = 'tersedia';
    public const STATUS_TERPESAN = 'terpesan';
    public const STATUS_TERISI   = 'terisi';
    public const STATUS = [self::STATUS_TERSEDIA, self::STATUS_TERPESAN, self::STATUS_TERISI];

    protected $table = 'slot_parkir';

    protected $fillable = ['lokasi_parkir_id', 'kode', 'tipe', 'status'];

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(LokasiParkir::class, 'lokasi_parkir_id');
    }

    /** Slot dengan tipe tertentu (lihat konstanta TIPE_*). */
    public function scopeTipe(Builder $query, string $tipe): Builder
    {
        return $query->where('tipe', $tipe);
    }

    /** Slot yang masih kosong (status 'tersedia'). */
    public function scopeTersedia(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_TERSEDIA);
    }
}
