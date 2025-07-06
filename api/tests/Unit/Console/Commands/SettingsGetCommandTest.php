<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\SettingsGetCommand;
use App\Facades\Settings;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsGetCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_with_key_argument()
    {
        Setting::factory()->create([
            'key' => 'test.setting',
            'value' => 'test_value',
        ]);

        Settings::shouldReceive('has')
            ->with('test.setting')
            ->once()
            ->andReturn(true);

        Settings::shouldReceive('get')
            ->with('test.setting')
            ->once()
            ->andReturn('test_value');

        $this->artisan(SettingsGetCommand::class, ['key' => 'test.setting'])
            ->expectsOutput("Value for 'test.setting':")
            ->expectsOutput('test_value')
            ->assertExitCode(0);
    }

    public function test_handle_with_no_key_argument()
    {
        Setting::factory()->create([
            'key' => 'test.setting',
            'value' => 'test_value',
        ]);

        Settings::shouldReceive('has')
            ->with('test.setting')
            ->once()
            ->andReturn(true);

        Settings::shouldReceive('get')
            ->with('test.setting')
            ->once()
            ->andReturn('test_value');

        $this->artisan(SettingsGetCommand::class)
            ->expectsQuestion('Enter setting key:', 'test.setting')
            ->expectsOutput("Value for 'test.setting':")
            ->expectsOutput('test_value')
            ->assertExitCode(0);
    }

    public function test_handle_with_nonexistent_setting()
    {
        Settings::shouldReceive('has')
            ->with('nonexistent.setting')
            ->once()
            ->andReturn(false);

        $this->artisan(SettingsGetCommand::class, ['key' => 'nonexistent.setting'])
            ->expectsOutput("Setting 'nonexistent.setting' not found.")
            ->assertExitCode(1);
    }

    public function test_handle_with_array_value()
    {
        $arrayValue = ['key1' => 'value1', 'key2' => 'value2'];
        
        Setting::factory()->create([
            'key' => 'test.array',
            'value' => json_encode($arrayValue),
        ]);

        Settings::shouldReceive('has')
            ->with('test.array')
            ->once()
            ->andReturn(true);

        Settings::shouldReceive('get')
            ->with('test.array')
            ->once()
            ->andReturn($arrayValue);

        $this->artisan(SettingsGetCommand::class, ['key' => 'test.array'])
            ->expectsOutput("Value for 'test.array':")
            ->assertExitCode(0);
    }

    public function test_handle_with_object_value()
    {
        $object = (object) ['key' => 'value'];
        
        Setting::factory()->create([
            'key' => 'test.object',
            'value' => json_encode($object),
        ]);

        Settings::shouldReceive('has')
            ->with('test.object')
            ->once()
            ->andReturn(true);

        Settings::shouldReceive('get')
            ->with('test.object')
            ->once()
            ->andReturn($object);

        $this->artisan(SettingsGetCommand::class, ['key' => 'test.object'])
            ->expectsOutput("Value for 'test.object':")
            ->assertExitCode(0);
    }

    public function test_handle_with_boolean_value()
    {
        Setting::factory()->create([
            'key' => 'test.boolean',
            'value' => true,
        ]);

        Settings::shouldReceive('has')
            ->with('test.boolean')
            ->once()
            ->andReturn(true);

        Settings::shouldReceive('get')
            ->with('test.boolean')
            ->once()
            ->andReturn(true);

        $this->artisan(SettingsGetCommand::class, ['key' => 'test.boolean'])
            ->expectsOutput("Value for 'test.boolean':")
            ->expectsOutput('1')
            ->assertExitCode(0);
    }

    public function test_handle_with_numeric_value()
    {
        Setting::factory()->create([
            'key' => 'test.number',
            'value' => 123,
        ]);

        Settings::shouldReceive('has')
            ->with('test.number')
            ->once()
            ->andReturn(true);

        Settings::shouldReceive('get')
            ->with('test.number')
            ->once()
            ->andReturn(123);

        $this->artisan(SettingsGetCommand::class, ['key' => 'test.number'])
            ->expectsOutput("Value for 'test.number':")
            ->expectsOutput('123')
            ->assertExitCode(0);
    }
}