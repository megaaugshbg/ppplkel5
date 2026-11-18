<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlotParkir extends Model
{
    public const TIPE_REGULER = 'reguler';
    public const TIPE_WANITA  = 'wanita';
    public const TIPE = [self::TIPE_REGULER, self::TIPE_WANITA];

    protected $table = 'slot_parkir';

    protected $fillable = ['lokasi_parkir_id', 'kode', 'tipe'];

    public function lokasi(): BelongsTo
    {
        return $this->belongsTo(LokasiParkir::class, 'lokasi_parkir_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'slot_parkir_id');
    }

    /** Slot yang tidak sedang dipakai booking aktif (dipesan / parkir). */
    public function scopeTersedia(Builder $query): Builder
    {
        return $query->whereDoesntHave('bookings', fn (Builder $q) => $q->aktif());
    }
}
