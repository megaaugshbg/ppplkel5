<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('booking')->cascadeOnDelete();
            $table->string('metode', 20);
            $table->unsignedInteger('jumlah');
            $table->string('status', 20)->default('berhasil');
            $table->timestamp('dibayar_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
