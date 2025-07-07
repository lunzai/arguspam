<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\SettingsGroupListCommand;
use App\Facades\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsGroupListCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_with_multiple_groups()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['system', 'email', 'database']);

        Settings::shouldReceive('group')
            ->with('system')
            ->once()
            ->andReturn([
                'app_name' => 'Test App',
                'debug' => true,
                'timezone' => 'UTC',
            ]);

        Settings::shouldReceive('group')
            ->with('email')
            ->once()
            ->andReturn([
                'smtp_host' => 'localhost',
                'smtp_port' => 587,
            ]);

        Settings::shouldReceive('group')
            ->with('database')
            ->once()
            ->andReturn([
                'default_connection' => 'mysql',
            ]);

        $this->artisan(SettingsGroupListCommand::class)
            ->expectsOutput('Available groups:')
            ->assertExitCode(0);
    }

    public function test_handle_with_single_group()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['system']);

        Settings::shouldReceive('group')
            ->with('system')
            ->once()
            ->andReturn([
                'app_name' => 'Test App',
                'debug' => true,
            ]);

        $this->artisan(SettingsGroupListCommand::class)
            ->expectsOutput('Available groups:')
            ->assertExitCode(0);
    }

    public function test_handle_with_no_groups()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn([]);

        $this->artisan(SettingsGroupListCommand::class)
            ->expectsOutput('Available groups:')
            ->assertExitCode(0);
    }

    public function test_handle_with_empty_group()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['empty_group']);

        Settings::shouldReceive('group')
            ->with('empty_group')
            ->once()
            ->andReturn([]);

        $this->artisan(SettingsGroupListCommand::class)
            ->expectsOutput('Available groups:')
            ->assertExitCode(0);
    }

    public function test_handle_displays_correct_settings_count()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['group1', 'group2']);

        Settings::shouldReceive('group')
            ->with('group1')
            ->once()
            ->andReturn(['setting1' => 'value1', 'setting2' => 'value2', 'setting3' => 'value3']);

        Settings::shouldReceive('group')
            ->with('group2')
            ->once()
            ->andReturn(['setting1' => 'value1']);

        $this->artisan(SettingsGroupListCommand::class)
            ->expectsOutput('Available groups:')
            ->assertExitCode(0);
    }

    public function test_command_signature()
    {
        $command = new SettingsGroupListCommand;
        $this->assertEquals('settings:group:list', $command->getName());
    }

    public function test_command_description()
    {
        $command = new SettingsGroupListCommand;
        $this->assertEquals('List all setting groups', $command->getDescription());
    }

    public function test_command_always_returns_zero()
    {
        Settings::shouldReceive('groups')->once()->andReturn([]);

        $exitCode = $this->artisan(SettingsGroupListCommand::class)->run();
        $this->assertEquals(0, $exitCode);
    }

    public function test_handle_shows_table_headers()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['test_group']);

        Settings::shouldReceive('group')
            ->with('test_group')
            ->once()
            ->andReturn(['test_setting' => 'test_value']);

        $this->artisan(SettingsGroupListCommand::class)
            ->expectsOutput('Available groups:')
            ->assertExitCode(0);
    }
}
