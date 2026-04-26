<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetOperatorPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'operators:reset-passwords
                            {--confirm : Konfirmasi bahwa Anda benar-benar ingin mereset semua password operator}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset semua password operator (admin_satker) ke "polri2026". Gunakan flag --confirm untuk menjalankan.';

    /**
     * Default password yang akan di-set untuk semua operator.
     */
    private const DEFAULT_PASSWORD = 'polri2026';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! $this->option('confirm')) {
            $this->error('⛔ Perintah ini akan mereset password SEMUA operator.');
            $this->line('   Jalankan dengan flag --confirm untuk melanjutkan:');
            $this->line('   php artisan operators:reset-passwords --confirm');
            return self::FAILURE;
        }

        $this->info('🔄 Mereset password semua operator...');
        $this->newLine();

        $operators = User::where('role', User::ROLE_ADMIN_SATKER)->get();

        if ($operators->isEmpty()) {
            $this->warn('⚠️  Tidak ada akun operator yang ditemukan.');
            return self::SUCCESS;
        }

        $count = 0;

        /** @var \App\Models\User $operator */
        foreach ($operators as $operator) {
            // Assign plain-text — the 'hashed' cast in User model calls Hash::make() once
            $operator->password = self::DEFAULT_PASSWORD;
            $operator->save();

            $this->line("  ✔ Reset: <comment>{$operator->username}</comment> ({$operator->name})");
            $count++;
        }

        $this->newLine();
        $this->info("✅ Selesai. {$count} akun operator berhasil direset ke password default.");
        $this->line('   Password baru: <comment>' . self::DEFAULT_PASSWORD . '</comment>');

        return self::SUCCESS;
    }
}
