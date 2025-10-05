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
            $table->timestamp('scheduled_start_datetime')
                ->nullable()
                ->after('end_datetime');
            $table->renameColumn('is_admin', 'is_admin_account');
            $table->dropForeign(['checkin_by']);
            $table->dropColumn(['is_expired', 'is_terminated', 'is_checkin', 'checkin_at', 'checkin_by']);
            $table->foreignId('started_by')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('started_at')
                ->nullable()
                ->after('started_by');
            $table->dropForeign(['ended_by']);
            $table->dropColumn(['ended_by']);
            $table->foreignId('ended_by')
                ->nullable()
                ->after('started_at')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('ended_at')
                ->nullable()
                ->after('ended_by')
                ->change();
            $table->foreignId('cancelled_by')
                ->nullable()
                ->after('ended_at')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('cancelled_at')
                ->nullable()
                ->after('cancelled_by');
            $table->timestamp('expired_at')
                ->nullable()
                ->after('terminated_at');
            $table->timestamp('account_created_at')
                ->nullable()
                ->after('status');
            $table->timestamp('account_revoked_at')
                ->nullable()
                ->after('account_created_at');
            $table->string('ai_risk_rating', 20)
                ->nullable()
                ->after('account_name');
            $table->text('ai_note')
                ->nullable()
                ->after('ai_risk_rating');
            $table->timestamp('ai_reviewed_at')
                ->nullable()
                ->after('ai_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign(['started_by']);
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn([
                'scheduled_start_datetime',
                'started_by', 'started_at',
                'cancelled_by', 'cancelled_at',
                'expired_at',
                'account_created_at', 'account_revoked_at',
                'ai_risk_rating', 'ai_note', 'ai_reviewed_at',
            ]);
            $table->renameColumn('is_admin_account', 'is_admin');
            $table->boolean('is_checkin')
                ->default(false)
                ->after('status');
            $table->boolean('is_expired')
                ->default(false)
                ->after('is_checkin');
            $table->boolean('is_terminated')
                ->default(false)
                ->after('is_expired');
            $table->foreignId('checkin_by')
                ->nullable()
                ->after('is_terminated')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('checkin_at')
                ->nullable()
                ->after('checkin_by');
        });
    }
};
