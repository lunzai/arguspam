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
        Schema::table('sessions', function (Blueprint $table) {
            // Drop old column
            $table->dropColumn('jit_vault_path');

            // Add new column
            $table->foreignId('asset_account_id')
                ->nullable()
                ->after('asset_id')
                ->constrained()
                ->nullOnDelete();

            // Rename column
            $table->renameColumn('is_jit', 'is_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            // Drop new column
            $table->dropForeign(['asset_account_id']);
            $table->dropColumn('asset_account_id');

            // Restore old column
            $table->string('jit_vault_path', 200)
                ->nullable()
                ->after('account_name');

            // Rename column back
            $table->renameColumn('is_admin', 'is_jit');
        });
    }
};
