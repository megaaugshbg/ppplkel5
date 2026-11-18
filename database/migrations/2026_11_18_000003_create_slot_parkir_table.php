<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('slot_parkir', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lokasi_parkir_id')->constrained('lokasi_parkir')->cascadeOnDelete();
            $table->string('kode', 10);                  // contoh: A-12, W-03
            $table->string('tipe', 10)->default('reguler'); // reguler | wanita
            $table->timestamps();

            $table->unique(['lokasi_parkir_id', 'kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slot_parkir');
    }
};
