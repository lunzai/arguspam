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
        Schema::table('requests', function (Blueprint $table) {
            $table->unsignedMediumInteger('cancelled_by')
                ->nullable()
                ->after('rejected_at');
            $table->timestamp('cancelled_at')
                ->nullable()
                ->after('cancelled_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('cancelled_by');
            $table->dropColumn('cancelled_at');
        });
    }
};
