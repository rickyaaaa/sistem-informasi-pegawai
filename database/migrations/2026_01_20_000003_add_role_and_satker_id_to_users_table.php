<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // RBAC:
            // - super_admin: full access (satker_id nullable)
            // - admin_satker: scoped access to their own satker (satker_id required by middleware/policy)
            $table->enum('role', ['super_admin', 'admin_satker'])->default('admin_satker')->index();

            // Nullable for super admin; required for admin satker (enforced at app level).
            // Foreign key will be added after 'satker' table migration exists.
            $table->unsignedBigInteger('satker_id')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'satker_id']);
        });
    }
};

