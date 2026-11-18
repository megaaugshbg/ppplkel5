<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    public const METODE = ['QRIS', 'GoPay', 'OVO', 'DANA', 'Transfer Bank'];

    protected $table = 'pembayaran';

    protected $fillable = ['booking_id', 'metode', 'jumlah', 'status', 'dibayar_at'];

    protected function casts(): array
    {
        return [
            'jumlah'     => 'integer',
            'dibayar_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
