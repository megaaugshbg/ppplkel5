<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    public const DIPESAN             = 'dipesan';
    public const PARKIR              = 'parkir';
    public const MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    public const LUNAS               = 'lunas';

    protected $table = 'booking';

    protected $fillable = [
        'kode', 'user_id', 'slot_parkir_id', 'plat_nomor', 'status',
        'check_in_at', 'check_out_at', 'durasi', 'total',
    ];

    protected function casts(): array
    {
        return [
            'check_in_at'  => 'datetime',
            'check_out_at' => 'datetime',
            'durasi'       => 'integer',
            'total'        => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'kode';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function slot(): BelongsTo
    {
        return $this->belongsTo(SlotParkir::class, 'slot_parkir_id');
    }

    public function pembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class);
    }

    /** Booking yang masih memakai slot: sudah dipesan atau sedang parkir. */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->whereIn('status', [self::DIPESAN, self::PARKIR]);
    }
}
