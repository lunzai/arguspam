<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\SettingsListCommand;
use App\Facades\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsListCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_with_no_group_option()
    {
        Settings::shouldReceive('all')
            ->once()
            ->andReturn([
                'system' => [
                    'app_name' => 'Test App',
                    'debug' => true,
                ],
                'email' => [
                    'smtp_host' => 'localhost',
                    'smtp_port' => 587,
                ],
            ]);

        $this->artisan(SettingsListCommand::class)
            ->expectsOutput('Group: system')
            ->expectsOutput('Group: email')
            ->assertExitCode(0);
    }

    public function test_handle_with_valid_group_option()
    {
        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['system', 'email']);

        Settings::shouldReceive('group')
            ->with('system')
            ->once()
            ->andReturn([
                'app_name' => 'Test App',
                'debug' => true,
            ]);

        $this->artisan(SettingsListCommand::class, ['--group' => 'system'])
            ->assertExitCode(0);
    }

    public function test_handle_with_invalid_group_option()
    {
        Settings::shouldReceive('groups')
            ->twice()
            ->andReturn(['system', 'email']);

        Settings::shouldReceive('group')
            ->with('system')
            ->once()
            ->andReturn([
                'app_name' => 'Test App',
            ]);

        $this->artisan(SettingsListCommand::class, ['--group' => 'invalid'])
            ->expectsChoice('Group not found. Select a group:', 'system', ['system', 'email'])
            ->assertExitCode(0);
    }

    public function test_format_value_with_boolean()
    {
        $command = new SettingsListCommand;
        $reflectionMethod = new \ReflectionMethod($command, 'formatValue');
        $reflectionMethod->setAccessible(true);

        $this->assertEquals('true', $reflectionMethod->invoke($command, true));
        $this->assertEquals('false', $reflectionMethod->invoke($command, false));
    }

    public function test_format_value_with_array()
    {
        $command = new SettingsListCommand;
        $reflectionMethod = new \ReflectionMethod($command, 'formatValue');
        $reflectionMethod->setAccessible(true);

        $array = ['key1' => 'value1', 'key2' => 'value2'];
        $expected = json_encode($array);
        $this->assertEquals($expected, $reflectionMethod->invoke($command, $array));
    }

    public function test_format_value_with_string()
    {
        $command = new SettingsListCommand;
        $reflectionMethod = new \ReflectionMethod($command, 'formatValue');
        $reflectionMethod->setAccessible(true);

        $this->assertEquals('test string', $reflectionMethod->invoke($command, 'test string'));
        $this->assertEquals('123', $reflectionMethod->invoke($command, 123));
        $this->assertEquals('123.45', $reflectionMethod->invoke($command, 123.45));
    }
}
