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
            $table->renameColumn('ai_risk_rating', 'session_activity_risk');
            $table->string('deviation_risk', 20)
                ->nullable()
                ->after('session_activity_risk');
            $table->string('overall_risk', 20)
                ->nullable()
                ->after('deviation_risk');
            $table->integer('human_audit_confidence')
                ->nullable()
                ->after('overall_risk');
            $table->boolean('human_audit_required')
                ->nullable()
                ->after('human_audit_confidence');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->renameColumn('session_activity_risk', 'ai_risk_rating');
            $table->dropColumn('deviation_risk');
            $table->dropColumn('overall_risk');
            $table->dropColumn('human_audit_confidence');
            $table->dropColumn('human_audit_required');
        });
    }
};
