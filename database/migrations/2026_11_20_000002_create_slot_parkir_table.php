<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slot parkir per lahan. Jumlah slot per tipe dan slot kosong dihitung dari
 * tabel ini (tidak disimpan sebagai kolom di lokasi_parkir).
 * Dicek hasTable dulu supaya aman di database bersama.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('slot_parkir')) {
            return;
        }

        Schema::create('slot_parkir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lokasi_parkir_id')->constrained('lokasi_parkir')->cascadeOnDelete();
            $table->string('kode', 10);                          // mis. R-01, W-03
            $table->string('tipe', 10)->default('reguler');      // reguler | wanita | prioritas | ev
            $table->string('status', 10)->default('tersedia');   // tersedia | terpesan | terisi
            $table->timestamps();

            $table->unique(['lokasi_parkir_id', 'kode']);
            $table->index(['lokasi_parkir_id', 'tipe', 'status']); // untuk hitung slot per tipe
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_parkir');
    }
};
