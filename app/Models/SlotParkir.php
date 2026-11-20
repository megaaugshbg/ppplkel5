<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SlotParkir extends Model
{
    public const TIPE_REGULER   = 'reguler';
    public const TIPE_WANITA    = 'wanita';
    public const TIPE_PRIORITAS = 'prioritas';
    public const TIPE_EV        = 'ev';

    // Tipe yang sudah bisa dipesan. Prioritas menunggu verifikasi kartu disabilitas.
    public const TIPE = [self::TIPE_REGULER, self::TIPE_WANITA, self::TIPE_EV];

    public const SEMUA_TIPE = [self::TIPE_REGULER, self::TIPE_WANITA, self::TIPE_PRIORITAS, self::TIPE_EV];

    public const LABEL = [
        self::TIPE_REGULER   => 'Normal Parking',
        self::TIPE_WANITA    => 'Woman Parking',
        self::TIPE_PRIORITAS => 'Priority Parking',
        self::TIPE_EV        => 'EV Charging Parking',
    ];

    /** Jenis konektor charger yang didukung. */
    public const KONEKTOR = ['Type 2', 'CCS2', 'CHAdeMO'];

    public const STATUS_TERSEDIA = 'tersedia';
    public const STATUS_TERPESAN = 'terpesan';
    public const STATUS_TERISI   = 'terisi';
    public const STATUS = [self::STATUS_TERSEDIA, self::STATUS_TERPESAN, self::STATUS_TERISI];

    protected $table = 'slot_parkir';

    protected $fillable = ['lokasi_parkir_id', 'kode', 'tipe', 'konektor', 'daya_kw', 'lantai', 'status'];

    protected function casts(): array
    {
        return ['lantai' => 'integer', 'daya_kw' => 'integer'];
    }

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(LokasiParkir::class, 'lokasi_parkir_id');
    }

    public function scopeTipe(Builder $query, string $tipe): Builder
    {
        return $query->where('tipe', $tipe);
    }

    public function scopeTersedia(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_TERSEDIA);
    }

    /** Slot dengan konektor tertentu (khusus EV). */
    public function scopeKonektor(Builder $query, string $konektor): Builder
    {
        return $query->where('konektor', $konektor);
    }
}
