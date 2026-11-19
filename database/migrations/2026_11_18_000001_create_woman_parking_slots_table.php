<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('woman_parking_slots', function (Blueprint $table) {
            $table->id();
            $table->string('lokasi', 100);
            $table->string('kode_slot', 10);
            $table->unsignedInteger('tarif');
            $table->string('status', 20)->default('tersedia'); // tersedia | terisi
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['lokasi', 'kode_slot']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('woman_parking_slots');
    }
};
