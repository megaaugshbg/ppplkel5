<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lahan parkir (mal, gedung, dsb.) untuk beranda dan pencarian lokasi.
 * Dicek hasTable dulu supaya aman di database bersama.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lokasi_parkir')) {
            return;
        }

        Schema::create('lokasi_parkir', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('alamat');
            $table->string('area', 60)->nullable();          // mis. Surabaya Barat
            $table->string('kota', 60)->index();              // mis. Surabaya
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('foto')->nullable();               // path relatif dari public/, mis. images/mal/tunjungan.jpg
            $table->time('jam_buka')->nullable();             // kosong = ikuti kolom buka saja
            $table->time('jam_tutup')->nullable();
            $table->unsignedInteger('tarif');                 // rupiah per jam
            $table->boolean('buka')->default(true);           // false = ditutup manual
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lokasi_parkir');
    }
};
