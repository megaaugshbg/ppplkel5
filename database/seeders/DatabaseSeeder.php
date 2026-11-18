<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'no_hp' => '081234567890', 'password' => 'password'],
        );

        $this->call(LokasiParkirSeeder::class);
    }
}
