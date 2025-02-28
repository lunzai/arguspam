<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TABLE_ORG = 'orgs';
    private const TABLE_USER = 'users';
    private const TABLE_ORG_USER = 'org_user';
    private const TABLE_USER_GROUP = 'user_groups';
    private const TABLE_USER_GROUP_USER = 'user_group_user';
    private const TABLE_ASSET = 'assets';
    private const TABLE_ASSET_ACCOUNT = 'asset_accounts';
    private const TABLE_ASSET_ACCESS_GRANT = 'asset_access_grants';
    private const TABLE_REQUEST = 'requests';
    private const TABLE_SESSION = 'sessions';
    private const TABLE_SESSION_AUDIT = 'session_audits';
    private const TABLE_USER_ACCESS_RESTRICTION = 'user_access_restrictions';
    private const TABLE_ACTION_AUDIT = 'actions_audits';

    private $enumStatus = ['active', 'inactive'];
    private $enumRequestStatus = ['pending', 'approved', 'rejected', 'expired'];
    private $enumSessionStatus = ['scheduled', 'active', 'expired', 'terminated', 'ended'];
    private $enumAssetAccessRole = ['requester', 'approver'];
    private $enumRequestScope = ['ReadOnly', 'ReadWrite', 'DDL', 'DML', 'All'];
    private $enumDbms = ['mysql', 'postgresql', 'sqlserver', 'oracle', 'mongodb', 'redis', 'mariadb'];
    private $enumRiskRating = ['low', 'medium', 'high', 'critical'];
    private $enumRestrictionType = ['ip', 'location', 'time'];

    private function addBlameableColumns(Blueprint $table)
    {
        $table->unsignedMediumInteger('created_by');
        $table->timestamp('created_at')
            ->nullable()
            ->default(DB::raw('CURRENT_TIMESTAMP'));
        $table->unsignedMediumInteger('updated_by');
        $table->timestamp('updated_at')
            ->nullable()
            ->default(DB::raw('CURRENT_TIMESTAMP'));
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
            $table->enum('status', $this->enumStatus)
                ->default('active');
            $this->addBlameableColumns($table);
            $this->addSoftDeletes($table);
        });

        Schema::table(self::TABLE_USER, function (Blueprint $table) {
            $table->dropTimestamps();
            $table->boolean('two_factor_enabled')
                ->after('password')
                ->default(false);
            $table->enum('status', $this->enumStatus)
                ->default('active');
            $table->timestamp('last_login_at')
                ->nullable();
            $this->addBlameableColumns($table);
            $this->addSoftDeletes($table);
        });

        Schema::create(self::TABLE_ORG_USER, function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')
                ->delete('cascade');
            $table->foreignId('user_id')
                ->delete('cascade');
            $table->timestamp('joined_at');
            $table->unique(['org_id', 'user_id']);
        });


        Schema::create(self::TABLE_USER_GROUP, function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id');
            $table->string('name', 100);
            $table->text('description')
                ->nullable();
            $table->enum('status', $this->enumStatus)
                ->default('active');
            $this->addBlameableColumns($table);
            $this->addSoftDeletes($table);
        });

        Schema::create(self::TABLE_USER_GROUP_USER, function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_group_id');
            $table->foreignId('user_id');
            $table->unique(['user_group_id', 'user_id']);
        });

        Schema::create(self::TABLE_ASSET, function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id');
            $table->string('name', 100);
            $table->text('description')
                ->nullable();
            $table->enum('status', $this->enumStatus)
                ->default('active');
            $table->string('host', 255);
            $table->unsignedSmallInteger('port');
            $table->enum('dbms', $this->enumDbms);
            $this->addBlameableColumns($table);
            $this->addSoftDeletes($table);
        });

        Schema::create(self::TABLE_ASSET_ACCOUNT, function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')
                ->delete('cascade');
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
                ->delete('cascade');
            $table->foreignId('user_id')
                ->nullable();
            $table->foreignId('user_group_id')
                ->nullable();
            $table->enum('role', $this->enumAssetAccessRole);
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
                ->delete('restrict');
            $table->foreignId('asset_id')
                ->delete('restrict');
            $table->foreignId('asset_account_id')
                ->nullable()
                ->delete('restrict');
            $table->foreignId('requester_id')
                ->constrained('users')
                ->delete('restrict');
            $table->dateTimeTz('start_datetime');
            $table->dateTimeTz('end_datetime');
            $table->unsignedSmallInteger('duration');
            $table->text('reason');
            $table->text('intended_query')
                ->nullable();
            $table->enum('scope', $this->enumRequestScope);
            $table->boolean('is_access_sensitive_data')
                ->default(false);
            $table->text('sensitive_data_note')
                ->nullable();
            $table->text('approver_note')
                ->nullable();
            $table->enum('approver_risk_rating', $this->enumRiskRating)
                ->nullable();
            $table->text('ai_note')
                ->nullable();
            $table->enum('ai_risk_rating', $this->enumRiskRating)
                ->nullable();
            $table->enum('status', $this->enumRequestStatus);
            $table->unsignedMediumInteger('approved_by')
                ->nullable()
                ->constrained('users');
            $table->timestamp('approved_at')
                ->nullable()
                ->delete('restrict');
            $table->unsignedMediumInteger('rejected_by')
                ->nullable()
                ->constrained('users');
            $table->timestamp('rejected_at')
                ->nullable()
                ->delete('restrict');
            $this->addBlameableColumns($table);
        });

        Schema::create(self::TABLE_SESSION, function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')
                ->delete('restrict');
            $table->foreignId('request_id')
                ->delete('restrict');
            $table->foreignId('asset_id')
                ->delete('restrict');
            $table->foreignId('requester_id')
                ->constrained('users')
                ->delete('restrict');
            $table->foreignId('approver_id')
                ->constrained('users');
            $table->dateTimeTz('start_datetime');
            $table->dateTimeTz('end_datetime')
                ->nullable();
            $table->dateTimeTz('scheduled_end_datetime');
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
            $table->enum('status', $this->enumSessionStatus);
            $table->foreignId('checkin_by')
                ->nullable()
                ->constrained('users');
            $table->timestamp('checkin_at')
                ->nullable();
            $table->foreignId('terminated_by')
                ->nullable()
                ->constrained('users');
            $table->timestamp('terminated_at')
                ->nullable();
            $table->foreignId('ended_by')
                ->nullable()
                ->constrained('users');
            $table->timestamp('ended_at')
                ->nullable();
            $this->addBlameableColumns($table);
        });

        Schema::create(self::TABLE_SESSION_AUDIT, function (Blueprint $table) {
            $table->id();
            $table->foreignId('org_id')
                ->delete('cascade');
            $table->foreignId('session_id')
                ->delete('cascade');
            $table->foreignId('request_id')
                ->delete('cascade');
            $table->foreignId('asset_id')
                ->delete('cascade');
            $table->foreignId('user_id')
                ->delete('cascade');
            $table->text('query_text');
            $table->dateTimeTz('query_timestamp');
            $table->timestamp('created_at')
                ->nullable()
                ->default(DB::raw('CURRENT_TIMESTAMP'));
        });

        Schema::create(self::TABLE_USER_ACCESS_RESTRICTION, function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->delete('cascade');
            // $table->foreignId('user_group_id')
            //     ->delete('cascade');
            $table->enum('type', $this->enumRestrictionType);
            $table->json('value');
            $table->enum('status', $this->enumStatus)
                ->default('active');
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
                ->delete('cascade');
            $table->foreignId('user_id')
                ->nullable()
                ->delete('cascade');
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
                ->nullable();
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
        Schema::dropIfExists(self::TABLE_USER);
        Schema::dropIfExists(self::TABLE_ORG);
    }
};
