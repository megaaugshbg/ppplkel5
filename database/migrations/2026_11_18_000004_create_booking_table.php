<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();        // contoh: MKR-001
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('slot_parkir_id')->constrained('slot_parkir');
            $table->string('plat_nomor', 15);
            // dipesan -> parkir -> menunggu_pembayaran -> lunas
            $table->string('status', 20)->default('dipesan')->index();
            $table->timestamp('check_in_at')->nullable();
            $table->timestamp('check_out_at')->nullable();
            $table->unsignedSmallInteger('durasi')->nullable(); // jam, diisi saat check-out
            $table->unsignedInteger('total')->nullable();       // rupiah, diisi saat check-out
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking');
    }
};
