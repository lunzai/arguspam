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
        Schema::create('user_access_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('type', array_column(AccessRestrictionType::cases(), 'value'));
            $table->json('value');
            $table->enum('status', array_column(Status::cases(), 'value'))
                ->default(Status::ACTIVE->value);
            $table->unsignedMediumInteger('created_by')
                ->nullable();
            $table->unsignedMediumInteger('updated_by')
                ->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_access_restrictions');
    }
};
