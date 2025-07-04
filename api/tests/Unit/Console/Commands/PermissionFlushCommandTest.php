<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\PermissionFlush;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionFlushCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_with_no_permissions()
    {
        Permission::query()->delete();
        $this->assertDatabaseCount('permissions', 0);

        $this->artisan(PermissionFlush::class)
            ->expectsOutput('No permissions found in database.')
            ->assertExitCode(0);
    }

    public function test_handle_with_permissions_and_confirmation()
    {
        Permission::query()->delete();
        Permission::factory()->count(3)->create();
        $this->assertDatabaseCount('permissions', 3);

        $this->artisan(PermissionFlush::class)
            ->expectsOutput('Found 3 permissions in database.')
            ->expectsConfirmation('Are you sure you want to delete all permissions?', 'yes')
            ->expectsOutput('Deleting permissions...')
            ->expectsOutput('All permissions have been deleted.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('permissions', 0);
    }

    public function test_handle_with_permissions_and_declined_confirmation()
    {
        Permission::query()->delete();
        Permission::factory()->count(2)->create();
        $this->assertDatabaseCount('permissions', 2);

        $this->artisan(PermissionFlush::class)
            ->expectsOutput('Found 2 permissions in database.')
            ->expectsConfirmation('Are you sure you want to delete all permissions?', 'no')
            ->expectsOutput('Operation cancelled.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('permissions', 2);
    }

    public function test_handle_with_force_option()
    {
        Permission::query()->delete();
        Permission::factory()->count(5)->create();
        $this->assertDatabaseCount('permissions', 5);

        $this->artisan(PermissionFlush::class, ['--force' => true])
            ->expectsOutput('Found 5 permissions in database.')
            ->expectsOutput('Deleting permissions...')
            ->expectsOutput('All permissions have been deleted.')
            ->doesntExpectOutput('Are you sure you want to delete all permissions?')
            ->assertExitCode(0);

        $this->assertDatabaseCount('permissions', 0);
    }

    public function test_handle_with_f_option_shorthand()
    {
        Permission::query()->delete();
        Permission::factory()->count(1)->create();
        $this->assertDatabaseCount('permissions', 1);

        $this->artisan(PermissionFlush::class, ['-f' => true])
            ->expectsOutput('Found 1 permissions in database.')
            ->expectsOutput('Deleting permissions...')
            ->expectsOutput('All permissions have been deleted.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('permissions', 0);
    }

    public function test_handle_returns_success_constant()
    {
        Permission::query()->delete();
        $exitCode = $this->artisan(PermissionFlush::class)->run();
        $this->assertEquals(0, $exitCode);
    }

    public function test_command_signature()
    {
        $command = new PermissionFlush();
        $this->assertStringContainsString('permission:flush', $command->getName());
        $this->assertStringContainsString('force', $command->getDefinition()->getOption('force')->getName());
        $this->assertStringContainsString('f', $command->getDefinition()->getOption('force')->getShortcut());
    }

    public function test_command_description()
    {
        $command = new PermissionFlush();
        $this->assertEquals('Delete all permissions from the database', $command->getDescription());
    }
}