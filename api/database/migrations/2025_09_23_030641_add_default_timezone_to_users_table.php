<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('default_timezone', 50)->after('password')->nullable();
        });

        // Set all existing users to Asia/Singapore timezone
        DB::table('users')->update(['default_timezone' => 'Asia/Singapore']);

        // Change column to not nullable
        Schema::table('users', function (Blueprint $table) {
            $table->string('default_timezone', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('default_timezone');
        });
    }
};
