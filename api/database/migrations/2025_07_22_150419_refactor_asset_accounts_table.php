<?php

use App\Enums\AssetAccountType;
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
        Schema::table('asset_accounts', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn('name');
            $table->dropColumn('vault_path');
            $table->dropColumn('is_default');

            // Add new columns
            $table->text('username')->after('asset_id');
            $table->text('password')->after('username');
            $table->enum('type', array_column(AssetAccountType::cases(), 'value'))
                ->after('password')
                ->default(AssetAccountType::ADMIN->value);
            $table->timestamp('expires_at')->nullable()->after('type');
            $table->boolean('is_active')->default(true)->after('expires_at');
            if (!Schema::hasIndex('asset_accounts', 'idx_asset_id')) {
                $table->index('asset_id', 'idx_asset_id');
            }
            $table->unique(['asset_id', 'type'], 'unique_admin_per_asset')
                ->where('type', AssetAccountType::ADMIN->value);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_accounts', function (Blueprint $table) {
            // Drop unique constraint
            $table->dropUnique('unique_admin_per_asset');

            // Drop new columns
            $table->dropColumn(['username', 'password', 'type', 'expires_at', 'is_active']);

            // Restore old columns
            $table->string('name', 100)->after('id');
            $table->text('vault_path')->nullable()->after('name');
            $table->boolean('is_default')->default(false)->after('vault_path');
        });
    }
};
