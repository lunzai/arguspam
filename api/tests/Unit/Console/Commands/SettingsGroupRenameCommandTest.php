<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\SettingsGroupRenameCommand;
use App\Facades\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsGroupRenameCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_with_both_arguments_and_confirmation()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['old_group', 'other_group']);

        Settings::shouldReceive('renameGroup')
            ->with('old_group', 'new_group')
            ->once();

        $this->artisan(SettingsGroupRenameCommand::class, [
            'old' => 'old_group',
            'new' => 'new_group',
        ])
            ->expectsConfirmation("Rename group 'old_group' to 'new_group'?", 'yes')
            ->expectsOutput('Group renamed successfully.')
            ->assertExitCode(0);
    }

    public function test_handle_with_both_arguments_and_declined_confirmation()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['old_group', 'other_group']);

        Settings::shouldReceive('renameGroup')
            ->never();

        $this->artisan(SettingsGroupRenameCommand::class, [
            'old' => 'old_group',
            'new' => 'new_group',
        ])
            ->expectsConfirmation("Rename group 'old_group' to 'new_group'?", 'no')
            ->expectsOutput('Operation cancelled.')
            ->assertExitCode(0);
    }

    public function test_handle_with_old_argument_only()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['old_group', 'other_group']);

        Settings::shouldReceive('renameGroup')
            ->with('old_group', 'new_group')
            ->once();

        $this->artisan(SettingsGroupRenameCommand::class, ['old' => 'old_group'])
            ->expectsQuestion('Enter new group name:', 'new_group')
            ->expectsConfirmation("Rename group 'old_group' to 'new_group'?", 'yes')
            ->expectsOutput('Group renamed successfully.')
            ->assertExitCode(0);
    }

    public function test_handle_with_no_arguments()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['group1', 'group2', 'group3']);

        Settings::shouldReceive('renameGroup')
            ->with('group2', 'renamed_group')
            ->once();

        $this->artisan(SettingsGroupRenameCommand::class)
            ->expectsChoice('Select group to rename:', 'group2', ['group1', 'group2', 'group3'])
            ->expectsQuestion('Enter new group name:', 'renamed_group')
            ->expectsConfirmation("Rename group 'group2' to 'renamed_group'?", 'yes')
            ->expectsOutput('Group renamed successfully.')
            ->assertExitCode(0);
    }

    public function test_handle_with_nonexistent_old_group()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['group1', 'group2']);

        Settings::shouldReceive('renameGroup')
            ->never();

        $this->artisan(SettingsGroupRenameCommand::class, ['old' => 'nonexistent_group'])
            ->expectsOutput("Group 'nonexistent_group' not found.")
            ->assertExitCode(1);
    }

    public function test_handle_with_valid_old_group_but_no_new_name()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['existing_group', 'other_group']);

        Settings::shouldReceive('renameGroup')
            ->with('existing_group', 'new_name')
            ->once();

        $this->artisan(SettingsGroupRenameCommand::class, ['old' => 'existing_group'])
            ->expectsQuestion('Enter new group name:', 'new_name')
            ->expectsConfirmation("Rename group 'existing_group' to 'new_name'?", 'yes')
            ->expectsOutput('Group renamed successfully.')
            ->assertExitCode(0);
    }

    public function test_handle_with_empty_groups_list()
    {
        $this->markTestSkipped('Cannot test empty choice options in Laravel console testing');
    }

    public function test_command_signature()
    {
        $command = new SettingsGroupRenameCommand;
        $this->assertStringContainsString('settings:group:rename', $command->getName());
        $this->assertTrue($command->getDefinition()->hasArgument('old'));
        $this->assertTrue($command->getDefinition()->hasArgument('new'));
    }

    public function test_command_description()
    {
        $command = new SettingsGroupRenameCommand;
        $this->assertEquals('Rename a settings group', $command->getDescription());
    }

    public function test_command_always_returns_zero()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['test_group']);

        Settings::shouldReceive('renameGroup')
            ->once();

        $exitCode = $this->artisan(SettingsGroupRenameCommand::class, [
            'old' => 'test_group',
            'new' => 'new_group',
        ])
            ->expectsConfirmation("Rename group 'test_group' to 'new_group'?", 'yes')
            ->run();

        $this->assertEquals(0, $exitCode);
    }

    public function test_operation_cancelled_returns_zero()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['test_group']);

        $exitCode = $this->artisan(SettingsGroupRenameCommand::class, [
            'old' => 'test_group',
            'new' => 'new_group',
        ])
            ->expectsConfirmation("Rename group 'test_group' to 'new_group'?", 'no')
            ->run();

        $this->assertEquals(0, $exitCode);
    }
}
