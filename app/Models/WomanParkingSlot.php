<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WomanParkingSlot extends Model
{
    protected $fillable = ['lokasi', 'kode_slot', 'tarif', 'status', 'catatan'];
}
