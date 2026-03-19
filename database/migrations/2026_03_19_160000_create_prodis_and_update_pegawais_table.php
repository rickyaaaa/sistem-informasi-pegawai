<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create prodis table
        Schema::create('prodis', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kategori'); // 'SMA/SMK', 'Perguruan Tinggi', 'Umum'
            $table->timestamps();
        });

        // 2. Add new columns to pegawais
        Schema::table('pegawais', function (Blueprint $table) {
            $table->date('tgl_lahir')->nullable()->after('nik');
            $table->unsignedBigInteger('prodi_id')->nullable()->after('pendidikan');
            $table->date('tgl_kerja')->nullable()->after('prodi_id');
            $table->text('keterangan')->nullable()->after('status');

            $table->foreign('prodi_id')
                  ->references('id')
                  ->on('prodis')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropForeign(['prodi_id']);
            $table->dropColumn(['tgl_lahir', 'prodi_id', 'tgl_kerja', 'keterangan']);
        });

        Schema::dropIfExists('prodis');
    }
};
