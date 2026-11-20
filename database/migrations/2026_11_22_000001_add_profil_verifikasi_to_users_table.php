<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Data profil untuk verifikasi Woman Parking & Priority Parking.
 * Hanya MENAMBAH kolom (akun lama bernilai null); tiap kolom dicek dulu
 * supaya aman di database bersama.
 */
return new class extends Migration
{
    /** kolom => definisi */
    private function kolom(): array
    {
        return [
            'jenis_kelamin'            => fn (Blueprint $t) => $t->string('jenis_kelamin', 10)->nullable(),            // 'laki-laki' | 'perempuan'
            'nomor_kartu_disabilitas'  => fn (Blueprint $t) => $t->string('nomor_kartu_disabilitas')->nullable(),
            'status_kartu_disabilitas' => fn (Blueprint $t) => $t->string('status_kartu_disabilitas', 15)->nullable(), // 'terverifikasi'
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
