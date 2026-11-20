<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('slot_parkir', function (Blueprint $t) {
            $t->string('konektor', 20)->nullable()->after('tipe');            // Type 2, CCS2, CHAdeMO
            $t->unsignedSmallInteger('daya_kw')->nullable()->after('konektor');
        });

        Schema::table('lokasi_parkir', function (Blueprint $t) {
            $t->unsignedInteger('tarif_ev_kwh')->nullable()->after('tarif');  // Rp per kWh
        });
    }

    public function down(): void
    {
        Schema::table('slot_parkir', fn (Blueprint $t) => $t->dropColumn(['konektor', 'daya_kw']));
        Schema::table('lokasi_parkir', fn (Blueprint $t) => $t->dropColumn('tarif_ev_kwh'));
    }
};
