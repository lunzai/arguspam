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
        Schema::table('asset_accounts', function (Blueprint $table) {
            if (Schema::hasIndex('asset_accounts', 'unique_admin_per_asset')) {
                $table->dropUnique('unique_admin_per_asset');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
