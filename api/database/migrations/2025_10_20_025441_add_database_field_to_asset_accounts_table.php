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
            $table->json('databases')->nullable()->after('password')->comment('Database names for this account (null = all databases, array = specific databases)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_accounts', function (Blueprint $table) {
            $table->dropColumn('databases');
        });
    }
};
