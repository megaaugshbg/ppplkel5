<?php

return [

    /*
    | Saklar PENGEMBANGAN: abaikan jam_buka/jam_tutup supaya pemesanan bisa diuji
    | di luar jam operasional. Hanya berlaku jika APP_ENV=local; kolom `buka`
    | (penutupan manual) tetap dihormati. Lihat LokasiParkir::sedangBuka().
    */
    'abaikan_jam_operasional' => (bool) env('PARKIR_ABAIKAN_JAM', false),

];
