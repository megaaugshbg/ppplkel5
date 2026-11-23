<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Verifikasi SIM untuk Woman Parking. Hanya MENAMBAH kolom (akun lama bernilai null);
 * tiap kolom dicek dulu supaya aman di database bersama.
 * Foto SIM, nomor SIM, dan nama TIDAK disimpan; hanya status dan waktu verifikasi.
 */
return new class extends Migration
{
    /** kolom => definisi */
    private function kolom(): array
    {
        return [
            'status_verifikasi_sim' => fn (Blueprint $t) => $t->string('status_verifikasi_sim', 15)->nullable(), // 'terverifikasi'
            'sim_diverifikasi_at'   => fn (Blueprint $t) => $t->timestamp('sim_diverifikasi_at')->nullable(),
        ];
    }

    public function up(): void
    {
        foreach ($this->kolom() as $nama => $definisi) {
            if (! Schema::hasColumn('users', $nama)) {
                Schema::table('users', fn (Blueprint $t) => $definisi($t));
            }
        }
    }

    public function down(): void
    {
        foreach (array_keys($this->kolom()) as $nama) {
            if (Schema::hasColumn('users', $nama)) {
                Schema::table('users', fn (Blueprint $t) => $t->dropColumn($nama));
            }
        }
    }
};
