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
        Schema::create('session_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('flag', 50);
            $table->timestamp('created_at')
                ->nullable()
                ->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_flags');
    }
};
