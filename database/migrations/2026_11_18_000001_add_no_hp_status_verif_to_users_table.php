<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dicek dulu supaya aman dijalankan pada database yang kolomnya sudah ditambah manual.
        if (! Schema::hasColumn('users', 'no_hp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('no_hp', 20)->nullable()->unique()->after('email');
            });
        }

        if (! Schema::hasColumn('users', 'status_verif')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('status_verif')->default(false)->after('no_hp');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'no_hp')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['no_hp']);
                $table->dropColumn('no_hp');
            });
        }

        if (Schema::hasColumn('users', 'status_verif')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('status_verif');
            });
        }
    }
};
