<?php

use App\Models\Role;
use App\Services\PolicyPermissionService;
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
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable()->default(null);
            $table->timestamps();
            $table->index('name');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable()->default(null);
            $table->timestamps();
            $table->index('name');
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('permission_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unique(['role_id', 'permission_id']);
            $table->timestamps();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unique(['role_id', 'user_id']);
            $table->timestamps();
        });

        // seed permissions
        app(PolicyPermissionService::class)->syncPermissions();

        // TODO: move to seeder
        // create default admin role
        Role::create([
            'name' => config('pam.rbac.default_admin_role'),
            'description' => 'Default admin role',
        ]);
        Role::create([
            'name' => config('pam.rbac.default_user_role'),
            'description' => 'Default user role',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
    }
};
