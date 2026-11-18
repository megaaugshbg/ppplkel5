<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LokasiParkir extends Model
{
    protected $table = 'lokasi_parkir';

    protected $fillable = ['nama', 'alamat', 'tarif', 'jarak', 'buka'];

    protected function casts(): array
    {
        return [
            'tarif' => 'integer',
            'buka'  => 'boolean',
        ];
    }

    public function slots(): HasMany
    {
        return $this->hasMany(SlotParkir::class, 'lokasi_parkir_id');
    }
}
