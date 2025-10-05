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
            $table->timestamp('submitted_at')
                ->nullable()
                ->after('status');
            $table->timestamp('expired_at')
                ->nullable()
                ->after('cancelled_at');
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
            $table->dropColumn('submitted_at');
            $table->dropColumn('expired_at');
        });
    }
};
