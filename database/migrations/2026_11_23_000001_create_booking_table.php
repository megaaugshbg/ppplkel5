<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pesanan (booking) Penyewa: pesan -> bayar -> check in -> check-out.
 *
 * - Foreign key HANYA ke users. lokasi_parkir_id & slot_parkir_id sengaja tanpa
 *   foreign key (cukup index): lokasi/slot bisa diubah atau dihapus (slot ikut
 *   terhapus berantai dari lokasi), sedangkan riwayat booking harus tetap utuh.
 *   Karena itu data lokasi/slot/tarif disimpan sebagai snapshot saat memesan.
 * - Waktu (waktu_bayar, waktu_checkin, waktu_keluar) disimpan dalam UTC.
 * - Dicek hasTable dulu supaya aman di database bersama.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('booking')) {
            return;
        }

        Schema::create('booking', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 12)->unique();                 // MKR-000123 (= barcode check-out)
            $table->foreignId('user_id')->constrained('users');
            $table->unsignedBigInteger('lokasi_parkir_id')->index(); // tanpa foreign key (lihat di atas)
            $table->unsignedBigInteger('slot_parkir_id');            // tanpa foreign key, index gabungan di bawah

            // Snapshot saat memesan
            $table->string('nama_lokasi');
            $table->string('alamat_lokasi');
            $table->string('label_tipe', 30);                     // mis. Normal Parking
            $table->string('kode_slot', 10);
            $table->string('konektor', 20)->nullable();           // EV
            $table->unsignedSmallInteger('daya_kw')->nullable();  // EV
            $table->unsignedInteger('tarif');                     // Rp per jam
            $table->unsignedInteger('tarif_ev_kwh')->nullable();  // EV, Rp per kWh

            // terpesan | sudah_dibayar | sudah_checkin | selesai | dibatalkan
            $table->string('status', 20)->default('terpesan');

            // Pembayaran (di muka, sebelum check in)
            $table->unsignedTinyInteger('durasi')->nullable();    // jam yang dibayar
            $table->unsignedInteger('total')->nullable();         // Rp
            $table->string('metode', 20)->nullable();
            $table->string('no_transaksi', 30)->nullable();
            $table->dateTime('waktu_bayar')->nullable();          // UTC

            // Check in / check-out
            $table->dateTime('waktu_checkin')->nullable();        // UTC
            $table->dateTime('waktu_keluar')->nullable();         // UTC
            $table->unsignedSmallInteger('durasi_aktual')->nullable(); // jam, dibulatkan ke atas
            $table->unsignedSmallInteger('melebihi_jam')->nullable();
            $table->unsignedInteger('total_biaya')->nullable();   // = total (sudah dibayar)

            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['slot_parkir_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking');
    }
};
