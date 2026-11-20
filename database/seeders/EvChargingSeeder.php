<?php

namespace Database\Seeders;

use App\Models\LokasiParkir;
use App\Models\SlotParkir;
use Illuminate\Database\Seeder;

class EvChargingSeeder extends Seeder
{
    public function run(): void
    {
        // Pola konektor & daya, digilir per slot EV.
        $charger = [
            ['konektor' => 'CCS2',    'daya_kw' => 50],
            ['konektor' => 'Type 2',  'daya_kw' => 22],
            ['konektor' => 'CHAdeMO', 'daya_kw' => 50],
            ['konektor' => 'Type 2',  'daya_kw' => 7],
        ];

        LokasiParkir::whereHas('slots', fn ($q) => $q->tipe(SlotParkir::TIPE_EV))
            ->each(function (LokasiParkir $lokasi) use ($charger) {
                $lokasi->update(['tarif_ev_kwh' => $lokasi->tarif_ev_kwh ?? 2500]);

                $lokasi->slots()->tipe(SlotParkir::TIPE_EV)->orderBy('id')->get()
                    ->each(fn (SlotParkir $s, int $i) => $s->update($charger[$i % count($charger)]));
            });
    }
}
