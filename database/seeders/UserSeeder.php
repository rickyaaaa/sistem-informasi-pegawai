<?php

namespace Database\Seeders;

use App\Models\Satker;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ── 1. Akun Super Admin ─────────────────────────────────
        User::query()->firstOrCreate(
            ['username' => 'superadmin'],
            [
                'name'      => 'Super Admin',
                'password'  => Hash::make('superpassword'),
                'role'      => 'super_admin',
                'satker_id' => null,
                'status'    => 'active',
            ]
        );

        $this->command->info('✔ Akun superadmin berhasil dibuat.');

        // ── 2. Akun Operator untuk setiap Induk ─────────────────
        $indukSatkers = Satker::where('level', 'induk')->get();
        $created = 0;

        foreach ($indukSatkers as $satker) {
            $username = Str::slug($satker->nama_satker, '_');

            User::query()->firstOrCreate(
                ['username' => $username],
                [
                    'name'      => 'Admin ' . $satker->nama_satker,
                    'password'  => Hash::make('polri2026'),
                    'role'      => 'admin_satker',
                    'satker_id' => $satker->id,
                    'status'    => 'active',
                ]
            );

            $created++;
        }

        $this->command->info("✔ {$created} akun operator berhasil dibuat/diverifikasi.");
    }
}
