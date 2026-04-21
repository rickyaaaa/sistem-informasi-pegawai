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
        Schema::table('pegawais', function (Blueprint $table) {
            $table->enum('status_k2', ['K-II', 'Non K-II'])->default('Non K-II')->after('satker_id');
            $table->string('nomor_k2')->nullable()->after('status_k2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pegawais', function (Blueprint $table) {
            $table->dropColumn(['status_k2', 'nomor_k2']);
        });
    }
};
