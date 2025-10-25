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
        Schema::table('session_audits', function (Blueprint $table) {
            $table->dropForeign(['request_id']);
            $table->dropColumn('request_id');
            $table->string('username', 100)
                ->after('user_id');
            $table->renameColumn('query_text', 'query');
            $table->integer('count')
                ->default(0)
                ->after('query');
            $table->renameColumn('query_timestamp', 'first_timestamp');
            $table->timestamp('first_timestamp')
                ->nullable()
                ->change();
            $table->timestamp('last_timestamp')
                ->nullable()
                ->after('first_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('session_audits', function (Blueprint $table) {
            $table->foreignId('request_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->dropColumn('username');
            $table->renameColumn('query', 'query_text');
            $table->renameColumn('first_timestamp', 'query_timestamp');
            $table->dropColumn('count');
            $table->dropColumn('last_timestamp');
        });
    }
};
