<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            JurusanSeeder::class,
            JamSekolahSeeder::class,
            KelasSeeder::class,
            SiswaSeeder::class,
            AuthUserSeeder::class,
        ]);

        // User::factory(10)->create();

        // Removed the default admin creation as we now use AuthUserSeeder
    }
}
