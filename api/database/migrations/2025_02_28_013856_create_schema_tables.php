<?php

use App\Enums\AssetAccessRole;
use App\Enums\Dbms;
use App\Enums\RequestScope;
use App\Enums\RequestStatus;
use App\Enums\AccessRestrictionType;
use App\Enums\RiskRating;
use App\Enums\SessionStatus;
use App\Enums\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLE_ORG = 'orgs';
    private const TABLE_USER = 'users';
    private const TABLE_ORG_USER = 'org_user';
    private const TABLE_USER_GROUP = 'user_groups';
    private const TABLE_USER_GROUP_USER = 'user_user_group';
    private const TABLE_ASSET = 'assets';
    private const TABLE_ASSET_ACCOUNT = 'asset_accounts';
    private const TABLE_ASSET_ACCESS_GRANT = 'asset_access_grants';
    private const TABLE_REQUEST = 'requests';
    private const TABLE_SESSION = 'sessions';
    private const TABLE_SESSION_AUDIT = 'session_audits';
    private const TABLE_USER_ACCESS_RESTRICTION = 'user_access_restrictions';
    private const TABLE_ACTION_AUDIT = 'action_audits';

    private function addBlameableColumns(Blueprint $table)
    {
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
    }

    private function addSoftDeletes(Blueprint $table)
    {
        $table->unsignedMediumInteger('deleted_by')
            ->nullable();
        $table->softDeletes('deleted_at');
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(self::TABLE_ORG, function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')
                ->nullable();
            $table->enum('status', array_column(Status::cases(), 'value'))
                ->default(Status::ACTIVE->value);
            $this->addBlameableColumns($table);
            $this->addSoftDeletes($table);
        });

        Schema::table(self::TABLE_USER, function (Blueprint $table) {
            $table->dropTimestamps();
            $table->boolean('two_factor_enabled')
                ->after('password')
                ->default(false);
            $table->enum('status', array_column(Status::cases(), 'value'))
                ->default(Status::ACTIVE->value);
            $table->timestamp('last_login_at')
                ->nullable();
            $this->addBlameableColumns($table);
            $this->addSoftDeletes($table);
        });

        Schema::create(self::TABLE_ORG_USER, function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->timestamp('joined_at')
                ->useCurrent();
            $table->unique(['org_id', 'user_id']);
        });

        Schema::create(self::TABLE_USER_GROUP, function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('name', 100);
            $table->text('description')
                ->nullable();
            $table->enum('status', array_column(Status::cases(), 'value'))
                ->default(Status::ACTIVE->value);
            $this->addBlameableColumns($table);
            $this->addSoftDeletes($table);
        });

        Schema::create(self::TABLE_USER_GROUP_USER, function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_group_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unique(['user_group_id', 'user_id']);
        });

        Schema::create(self::TABLE_ASSET, function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')
                ->constrained()
                ->restrictOnDelete();
            $table->string('name', 100);
            $table->text('description')
                ->nullable();
            $table->enum('status', array_column(Status::cases(), 'value'))
                ->default(Status::ACTIVE->value);
            $table->string('host', 255);
            $table->unsignedSmallInteger('port');
            $table->enum('dbms', array_column(Dbms::cases(), 'value'));
            $this->addBlameableColumns($table);
            $this->addSoftDeletes($table);
        });

        Schema::create(self::TABLE_ASSET_ACCOUNT, function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('name', 100);
            $table->text('vault_path')
                ->nullable();
            $table->boolean('is_default')
                ->default(false);
            $this->addBlameableColumns($table);
        });

        Schema::create(self::TABLE_ASSET_ACCESS_GRANT, function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_group_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('role', array_column(AssetAccessRole::cases(), 'value'));
            $this->addBlameableColumns($table);
            $table->unique(['asset_id', 'user_id', 'user_group_id', 'role']);
        });

        DB::statement(sprintf('
            ALTER TABLE %1$s 
            ADD CONSTRAINT %1$s_check_user_or_user_group 
            CHECK (user_id IS NOT NULL OR user_group_id IS NOT NULL)
        ', self::TABLE_ASSET_ACCESS_GRANT));

        Schema::create(self::TABLE_REQUEST, function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('asset_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('asset_account_id')
                ->nullable()
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('requester_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('start_datetime');
            $table->timestamp('end_datetime');
            $table->unsignedSmallInteger('duration');
            $table->text('reason');
            $table->text('intended_query')
                ->nullable();
            $table->enum('scope', array_column(RequestScope::cases(), 'value'))
                ->default(RequestScope::READ_ONLY->value);
            $table->boolean('is_access_sensitive_data')
                ->default(false);
            $table->text('sensitive_data_note')
                ->nullable();
            $table->text('approver_note')
                ->nullable();
            $table->enum('approver_risk_rating', array_column(RiskRating::cases(), 'value'))
                ->nullable();
            $table->text('ai_note')
                ->nullable();
            $table->enum('ai_risk_rating', array_column(RiskRating::cases(), 'value'))
                ->nullable();
            $table->enum('status', array_column(RequestStatus::cases(), 'value'))
                ->default(RequestStatus::PENDING->value);
            $table->unsignedMediumInteger('approved_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('approved_at')
                ->nullable();
            $table->unsignedMediumInteger('rejected_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('rejected_at')
                ->nullable();
            $this->addBlameableColumns($table);
        });

        Schema::create(self::TABLE_SESSION, function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('request_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('asset_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('requester_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('approver_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('start_datetime');
            $table->timestamp('end_datetime')
                ->nullable();
            $table->timestamp('scheduled_end_datetime');
            $table->unsignedSmallInteger('requested_duration');
            $table->unsignedSmallInteger('actual_duration')
                ->nullable();
            $table->boolean('is_jit')
                ->default(false);
            $table->string('account_name', 100)
                ->nullable();
            $table->string('jit_vault_path', 200)
                ->nullable();
            $table->text('session_note')
                ->nullable();
            $table->boolean('is_expired')
                ->default(false);
            $table->boolean('is_terminated')
                ->default(false);
            $table->boolean('is_checkin')
                ->default(false);
            $table->enum('status', array_column(SessionStatus::cases(), 'value'))
                ->default(SessionStatus::SCHEDULED->value);
            $table->foreignId('checkin_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('checkin_at')
                ->nullable();
            $table->foreignId('terminated_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('terminated_at')
                ->nullable();
            $table->foreignId('ended_by')
                ->nullable()
                ->constrained('users')
                ->restrictOnDelete();
            $table->timestamp('ended_at')
                ->nullable();
            $this->addBlameableColumns($table);
        });

        Schema::create(self::TABLE_SESSION_AUDIT, function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('session_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('request_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('asset_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->text('query_text');
            $table->timestamp('query_timestamp');
            $table->timestamp('created_at')
                ->nullable()
                ->useCurrent();
        });

        Schema::create(self::TABLE_USER_ACCESS_RESTRICTION, function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->enum('type', array_column(AccessRestrictionType::cases(), 'value'));
            $table->json('value');
            $table->enum('status', array_column(Status::cases(), 'value'))
                ->default(Status::ACTIVE->value);
            $this->addBlameableColumns($table);
        });

        // DB::statement(sprintf('
        //     ALTER TABLE %1$s
        //     ADD CONSTRAINT %1$s_check_user_or_user_group
        //     CHECK (user_id IS NOT NULL OR user_group_id IS NOT NULL)
        // ', self::TABLE_USER_ACCESS_RESTRICTION));

        Schema::create(self::TABLE_ACTION_AUDIT, function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('action_type', 100);
            $table->string('entity_type', 100)
                ->nullable();
            $table->unsignedInteger('entity_id')
                ->nullable();
            $table->text('description');
            $table->json('previous_state')
                ->nullable();
            $table->json('new_state')
                ->nullable();
            $table->string('ip_address', 50)
                ->nullable();
            $table->text('user_agent')
                ->nullable();
            $table->json('additional_data')
                ->nullable();
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
        Schema::dropIfExists(self::TABLE_ACTION_AUDIT);
        Schema::dropIfExists(self::TABLE_USER_ACCESS_RESTRICTION);
        Schema::dropIfExists(self::TABLE_SESSION_AUDIT);
        Schema::dropIfExists(self::TABLE_SESSION);
        Schema::dropIfExists(self::TABLE_REQUEST);
        Schema::dropIfExists(self::TABLE_ASSET_ACCESS_GRANT);
        Schema::dropIfExists(self::TABLE_ASSET_ACCOUNT);
        Schema::dropIfExists(self::TABLE_ASSET);
        Schema::dropIfExists(self::TABLE_USER_GROUP_USER);
        Schema::dropIfExists(self::TABLE_USER_GROUP);
        Schema::dropIfExists(self::TABLE_ORG_USER);
        Schema::dropIfExists(self::TABLE_ORG);
    }
};
