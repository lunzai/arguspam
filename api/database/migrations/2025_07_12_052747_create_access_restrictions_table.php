<?php

use App\Enums\AccessRestrictionType;
use App\Enums\Status;
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
        Schema::create('access_restrictions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')
                ->nullable();
            $table->enum('type', array_column(AccessRestrictionType::cases(), 'value'));
            $table->json('data');
            $table->enum('status', array_column(Status::cases(), 'value'))
                ->default(Status::ACTIVE->value);
            $table->integer('weight')
                ->default(0);
            $table->unsignedMediumInteger('created_by')
                ->nullable();
            $table->timestamp('created_at')
                ->nullable()
                ->useCurrent();
            $table->unsignedMediumInteger('updated_by')
                ->nullable();
            $table->timestamp('updated_at')
                ->nullable()
                ->useCurrentOnUpdate()
                ->useCurrent();
        });

        Schema::create('access_restriction_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_restriction_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unique(['access_restriction_id', 'user_id'], 'access_restriction_user_unique');
        });

        Schema::create('access_restriction_user_group', function (Blueprint $table) {
            $table->id();
            $table->foreignId('access_restriction_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_group_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unique(['access_restriction_id', 'user_group_id'], 'access_restriction_user_group_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('access_restrictions');
        Schema::dropIfExists('access_restriction_user');
        Schema::dropIfExists('access_restriction_user_group');
        Schema::enableForeignKeyConstraints();
    }
};
