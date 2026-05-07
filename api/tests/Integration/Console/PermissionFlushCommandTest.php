<?php

namespace Tests\Integration\Console;

use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionFlushCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_deletes_all_permissions_with_force_flag(): void
    {
        Permission::factory()->count(3)->create();
        $this->assertDatabaseCount('permissions', 3);

        $this->artisan('permission:flush', ['--force' => true])
            ->assertExitCode(0);

        $this->assertDatabaseCount('permissions', 0);
    }

    public function test_command_reports_success_message(): void
    {
        Permission::factory()->count(2)->create();

        $this->artisan('permission:flush', ['--force' => true])
            ->expectsOutputToContain('deleted')
            ->assertExitCode(0);
    }

    public function test_command_exits_successfully_when_no_permissions(): void
    {
        $this->artisan('permission:flush', ['--force' => true])
            ->assertExitCode(0);
    }

    public function test_command_cancels_without_force_when_user_declines(): void
    {
        Permission::factory()->count(2)->create();

        $this->artisan('permission:flush')
            ->expectsConfirmation('Are you sure you want to delete all permissions?', 'no')
            ->assertExitCode(0);

        $this->assertDatabaseCount('permissions', 2);
    }
}
