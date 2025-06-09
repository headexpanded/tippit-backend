<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            TeamSeeder::class,
            RoundSeeder::class,
            GameSeeder::class,
            UserAndLeagueSeeder::class,
        ]);
    }
}
