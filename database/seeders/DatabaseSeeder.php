<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SatkerSatwilSeeder::class,  // 1. Hirarki Satker & Satwil
            UserSeeder::class,          // 2. Superadmin + 43 Operator
        ]);
    }
}
