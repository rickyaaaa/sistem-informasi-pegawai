<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::table('pegawais')->where('jenis_kelamin', 'Laki-laki')->update(['jenis_kelamin' => 'Pria']);
        \Illuminate\Support\Facades\DB::table('pegawais')->where('jenis_kelamin', 'Perempuan')->update(['jenis_kelamin' => 'Wanita']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::table('pegawais')->where('jenis_kelamin', 'Pria')->update(['jenis_kelamin' => 'Laki-laki']);
        \Illuminate\Support\Facades\DB::table('pegawais')->where('jenis_kelamin', 'Wanita')->update(['jenis_kelamin' => 'Perempuan']);
    }
};
