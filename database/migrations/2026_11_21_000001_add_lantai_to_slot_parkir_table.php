<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambah kolom lantai ke slot_parkir untuk denah per lantai.
 * Hanya MENAMBAH kolom; dicek dulu supaya aman di database bersama.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('slot_parkir', 'lantai')) {
            Schema::table('slot_parkir', function (Blueprint $table) {
                $table->unsignedTinyInteger('lantai')->default(1)->after('tipe');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('slot_parkir', 'lantai')) {
            Schema::table('slot_parkir', function (Blueprint $table) {
                $table->dropColumn('lantai');
            });
        }
    }
};
