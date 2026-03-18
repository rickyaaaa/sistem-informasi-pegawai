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
        Schema::table('satkers', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->string('tipe_satuan')->default('satker')->after('nama_satker'); // 'satker' or 'satwil'
            $table->string('level')->default('induk')->after('tipe_satuan');         // 'induk' or 'sub'

            $table->foreign('parent_id')
                  ->references('id')
                  ->on('satkers')
                  ->onDelete('cascade');

            // Drop unique constraint on nama_satker (sub-units like SUBBAGRENMIN repeat)
            $table->dropUnique(['nama_satker']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('satkers', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'tipe_satuan', 'level']);
            $table->unique('nama_satker');
        });
    }
};
