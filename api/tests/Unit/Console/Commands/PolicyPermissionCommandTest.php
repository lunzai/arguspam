<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\PolicyPermission;
use App\Services\PolicyPermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyPermissionCommandTest extends TestCase
{
    use RefreshDatabase;

    protected PolicyPermissionService $mockService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockService = $this->createMock(PolicyPermissionService::class);
        $this->app->instance(PolicyPermissionService::class, $this->mockService);
    }

    public function test_handle_with_no_changes()
    {
        $changes = [
            'to_add' => collect(),
            'to_remove' => collect(),
            'unchanged' => collect(),
        ];

        $this->mockService->expects($this->once())
            ->method('getChanges')
            ->with(false)
            ->willReturn($changes);

        $this->mockService->expects($this->once())
            ->method('syncPermissions')
            ->with(false);

        $this->artisan(PolicyPermission::class)
            ->expectsOutput('No permissions found in policies.')
            ->expectsOutput('Permissions synced successfully!')
            ->assertExitCode(0);
    }

    public function test_handle_with_new_permissions()
    {
        $changes = [
            'to_add' => collect([
                ['name' => 'users.create', 'description' => 'Create users'],
                ['name' => 'users.update', 'description' => 'Update users'],
            ]),
            'to_remove' => collect(),
            'unchanged' => collect([
                ['name' => 'users.view', 'description' => 'View users'],
            ]),
        ];

        $this->mockService->expects($this->once())
            ->method('getChanges')
            ->with(false)
            ->willReturn($changes);

        $this->mockService->expects($this->once())
            ->method('syncPermissions')
            ->with(false);

        $this->artisan(PolicyPermission::class)
            ->expectsOutput('Permissions synced successfully!')
            ->assertExitCode(0);
    }

    public function test_handle_with_remove_others_option_and_confirmation()
    {
        $changes = [
            'to_add' => collect([
                ['name' => 'new.permission', 'description' => 'New permission'],
            ]),
            'to_remove' => collect([
                ['name' => 'old.permission', 'description' => 'Old permission'],
            ]),
            'unchanged' => collect(),
        ];

        $this->mockService->expects($this->once())
            ->method('getChanges')
            ->with(true)
            ->willReturn($changes);

        $this->mockService->expects($this->once())
            ->method('syncPermissions')
            ->with(true);

        $this->artisan(PolicyPermission::class, ['--remove-others' => true])
            ->expectsConfirmation('This will remove existing permissions. Continue?', 'yes')
            ->expectsOutput('Permissions synced successfully!')
            ->assertExitCode(0);
    }

    public function test_handle_with_remove_others_option_and_declined_confirmation()
    {
        $changes = [
            'to_add' => collect(),
            'to_remove' => collect([
                ['name' => 'old.permission', 'description' => 'Old permission'],
            ]),
            'unchanged' => collect(),
        ];

        $this->mockService->expects($this->once())
            ->method('getChanges')
            ->with(true)
            ->willReturn($changes);

        $this->mockService->expects($this->never())
            ->method('syncPermissions');

        $this->artisan(PolicyPermission::class, ['--remove-others' => true])
            ->expectsConfirmation('This will remove existing permissions. Continue?', 'no')
            ->assertExitCode(1);
    }

    public function test_handle_with_dry_run_option()
    {
        $changes = [
            'to_add' => collect([
                ['name' => 'new.permission', 'description' => 'New permission'],
            ]),
            'to_remove' => collect(),
            'unchanged' => collect(),
        ];

        $this->mockService->expects($this->once())
            ->method('getChanges')
            ->with(false)
            ->willReturn($changes);

        $this->mockService->expects($this->never())
            ->method('syncPermissions');

        $this->artisan(PolicyPermission::class, ['--dry-run' => true])
            ->expectsOutput('Dry run complete. No changes were made.')
            ->assertExitCode(0);
    }

    public function test_handle_with_remove_others_but_no_permissions_to_remove()
    {
        $changes = [
            'to_add' => collect([
                ['name' => 'new.permission', 'description' => 'New permission'],
            ]),
            'to_remove' => collect(),
            'unchanged' => collect(),
        ];

        $this->mockService->expects($this->once())
            ->method('getChanges')
            ->with(true)
            ->willReturn($changes);

        $this->mockService->expects($this->once())
            ->method('syncPermissions')
            ->with(true);

        $this->artisan(PolicyPermission::class, ['--remove-others' => true])
            ->expectsOutput('Permissions synced successfully!')
            ->assertExitCode(0);
    }

    public function test_show_changes_displays_table_correctly()
    {
        $changes = [
            'to_add' => collect([
                ['name' => 'users.create', 'description' => 'Create users'],
            ]),
            'to_remove' => collect([
                ['name' => 'old.permission', 'description' => 'Old permission'],
            ]),
            'unchanged' => collect([
                ['name' => 'users.view', 'description' => 'View users'],
            ]),
        ];

        $this->mockService->expects($this->once())
            ->method('getChanges')
            ->with(false)
            ->willReturn($changes);

        $this->mockService->expects($this->once())
            ->method('syncPermissions')
            ->with(false);

        $this->artisan(PolicyPermission::class)
            ->expectsOutput('Permissions synced successfully!')
            ->assertExitCode(0);
    }

    public function test_command_signature()
    {
        $command = new PolicyPermission($this->mockService);
        $this->assertStringContainsString('permission:sync', $command->getName());
        $this->assertTrue($command->getDefinition()->hasOption('remove-others'));
        $this->assertTrue($command->getDefinition()->hasOption('dry-run'));
    }

    public function test_command_description()
    {
        $command = new PolicyPermission($this->mockService);
        $this->assertEquals('Sync permissions from policies to database', $command->getDescription());
    }

    public function test_constructor_injection()
    {
        $service = $this->createMock(PolicyPermissionService::class);
        $command = new PolicyPermission($service);

        $reflection = new \ReflectionClass($command);
        $serviceProperty = $reflection->getProperty('service');
        $serviceProperty->setAccessible(true);

        $this->assertSame($service, $serviceProperty->getValue($command));
    }
}
