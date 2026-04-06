<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('org_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained()->cascadeOnDelete();
            $table->string('key', 191);
            $table->json('value')->nullable();
            $table->timestamps();
            $table->unique(['org_id', 'key']);
        });

        Schema::create('org_ai_providers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('driver', 64);
            $table->string('label', 191)->nullable();
            $table->json('options')->nullable();
            $table->text('api_key');
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
            $table->index(['org_id', 'sort_order']);
        });

        Schema::create('org_ai_agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')->constrained()->cascadeOnDelete();
            $table->string('role', 64);
            $table->json('failover');
            $table->decimal('temperature', 4, 3)->nullable();
            $table->unsignedInteger('max_output_tokens')->nullable();
            $table->unsignedSmallInteger('request_timeout_seconds')->nullable();
            $table->json('provider_metadata')->nullable();
            $table->timestamps();
            $table->unique(['org_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('org_ai_agents');
        Schema::dropIfExists('org_ai_providers');
        Schema::dropIfExists('org_settings');
    }
};
