<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawai_requests', function (Blueprint $table) {
            $table->id();

            // The pegawai being modified (null for create requests)
            $table->foreignId('pegawai_id')
                  ->nullable()
                  ->constrained('pegawais')
                  ->nullOnDelete();

            // The satker this request belongs to
            $table->foreignId('satker_id')
                  ->constrained('satkers');

            // Who submitted the request
            $table->foreignId('requested_by')
                  ->constrained('users');

            // What action is being requested
            $table->enum('action_type', ['create', 'update', 'delete']);

            // Snapshot of the data (includes file paths)
            $table->json('data_payload')->nullable();

            // Workflow status
            $table->enum('status', ['pending', 'approved', 'rejected'])
                  ->default('pending')
                  ->index();

            // Who processed the request
            $table->foreignId('approved_by')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();

            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawai_requests');
    }
};
