<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\Satker;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create sample Satker records (for thesis demo & screenshots).
        $satkerUmum = Satker::query()->firstOrCreate(['nama_satker' => 'Bagian Umum']);
        $satkerKeuangan = Satker::query()->firstOrCreate(['nama_satker' => 'Bagian Keuangan']);
        $satkerKepegawaian = Satker::query()->firstOrCreate(['nama_satker' => 'Bagian Kepegawaian']);

        // Default credentials for local development / thesis demo.
        // Change these values in production.
        User::query()->firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_SUPER_ADMIN,
                'satker_id' => null,
            ]
        );

        // Sample admin satker account.
        User::query()->firstOrCreate(
            ['email' => 'admin.umum@example.com'],
            [
                'name' => 'Admin Satker Umum',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN_SATKER,
                'satker_id' => $satkerUmum->id,
            ]
        );

        // Sample Pegawai data.
        if (Pegawai::query()->count() === 0) {
            Pegawai::factory()->count(8)->create(['satker_id' => $satkerUmum->id]);
            Pegawai::factory()->count(6)->create(['satker_id' => $satkerKeuangan->id]);
            Pegawai::factory()->count(6)->create(['satker_id' => $satkerKepegawaian->id]);
        }
    }
}
