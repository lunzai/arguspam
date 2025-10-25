<?php

use App\Enums\AccessRestrictionType;
use App\Enums\AssetAccessRole;
use App\Enums\AssetAccountType;
use App\Enums\DatabaseScope;
use App\Enums\Dbms;
use App\Enums\RequestStatus;
use App\Enums\RiskRating;
use App\Enums\SessionStatus;
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
        // Change orgs.status
        Schema::table('orgs', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });

        // Change users.status
        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });

        // Change user_groups.status
        Schema::table('user_groups', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });

        // Change assets.status and assets.dbms
        Schema::table('assets', function (Blueprint $table) {
            $table->string('status', 20)->change();
            $table->string('dbms', 20)->change();
        });

        // Change asset_accounts.type
        Schema::table('asset_accounts', function (Blueprint $table) {
            $table->string('type', 20)->change();
        });

        // Change asset_access_grants.role
        Schema::table('asset_access_grants', function (Blueprint $table) {
            $table->string('role', 20)->change();
        });

        // Change requests enum columns
        Schema::table('requests', function (Blueprint $table) {
            $table->string('scope', 20)->change();
            $table->string('approver_risk_rating', 20)->nullable()->change();
            $table->string('ai_risk_rating', 20)->nullable()->change();
            $table->string('status', 20)->change();
        });

        // Change sessions.status
        Schema::table('sessions', function (Blueprint $table) {
            $table->string('status', 20)->change();
        });

        // Change user_access_restrictions enum columns
        Schema::table('user_access_restrictions', function (Blueprint $table) {
            $table->string('type', 20)->change();
            $table->string('status', 20)->change();
        });

        // Change access_restrictions enum columns
        Schema::table('access_restrictions', function (Blueprint $table) {
            $table->string('type', 20)->change();
            $table->string('status', 20)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert orgs.status
        Schema::table('orgs', function (Blueprint $table) {
            $table->enum('status', array_column(Status::cases(), 'value'))->default(Status::ACTIVE->value)->change();
        });

        // Revert users.status
        Schema::table('users', function (Blueprint $table) {
            $table->enum('status', array_column(Status::cases(), 'value'))->default(Status::ACTIVE->value)->change();
        });

        // Revert user_groups.status
        Schema::table('user_groups', function (Blueprint $table) {
            $table->enum('status', array_column(Status::cases(), 'value'))->default(Status::ACTIVE->value)->change();
        });

        // Revert assets.status and assets.dbms
        Schema::table('assets', function (Blueprint $table) {
            $table->enum('status', array_column(Status::cases(), 'value'))->default(Status::ACTIVE->value)->change();
            $table->enum('dbms', array_column(Dbms::cases(), 'value'))->change();
        });

        // Revert asset_accounts.type
        Schema::table('asset_accounts', function (Blueprint $table) {
            $table->enum('type', array_column(AssetAccountType::cases(), 'value'))->default(AssetAccountType::ADMIN->value)->change();
        });

        // Revert asset_access_grants.role
        Schema::table('asset_access_grants', function (Blueprint $table) {
            $table->enum('role', array_column(AssetAccessRole::cases(), 'value'))->change();
        });

        // Revert requests enum columns
        Schema::table('requests', function (Blueprint $table) {
            $table->enum('scope', array_column(DatabaseScope::cases(), 'value'))->default(DatabaseScope::READ_ONLY->value)->change();
            $table->enum('approver_risk_rating', array_column(RiskRating::cases(), 'value'))->nullable()->change();
            $table->enum('ai_risk_rating', array_column(RiskRating::cases(), 'value'))->nullable()->change();
            $table->enum('status', array_column(RequestStatus::cases(), 'value'))->default(RequestStatus::PENDING->value)->change();
        });

        // Revert sessions.status
        Schema::table('sessions', function (Blueprint $table) {
            $table->enum('status', array_column(SessionStatus::cases(), 'value'))->default(SessionStatus::SCHEDULED->value)->change();
        });

        // Revert user_access_restrictions enum columns
        Schema::table('user_access_restrictions', function (Blueprint $table) {
            $table->enum('type', array_column(AccessRestrictionType::cases(), 'value'))->change();
            $table->enum('status', array_column(Status::cases(), 'value'))->default(Status::ACTIVE->value)->change();
        });

        // Revert access_restrictions enum columns
        Schema::table('access_restrictions', function (Blueprint $table) {
            $table->enum('type', array_column(AccessRestrictionType::cases(), 'value'))->change();
            $table->enum('status', array_column(Status::cases(), 'value'))->default(Status::ACTIVE->value)->change();
        });
    }
};
