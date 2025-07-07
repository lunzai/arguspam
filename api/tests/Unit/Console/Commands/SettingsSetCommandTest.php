<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\SettingsSetCommand;
use App\Enums\SettingDataType;
use App\Facades\Settings;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsSetCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_with_key_and_value_arguments()
    {
        $setting = Setting::factory()->create([
            'key' => 'test.setting',
            'key_slug' => 'test_setting',
            'data_type' => SettingDataType::STRING,
            'value' => 'old_value',
        ]);

        Settings::shouldReceive('get')
            ->with('test.setting')
            ->once()
            ->andReturn('old_value');

        Settings::shouldReceive('set')
            ->with('test.setting', 'new_value')
            ->once();

        $this->artisan(SettingsSetCommand::class, [
            'key' => 'test.setting',
            'value' => 'new_value',
        ])
            ->expectsConfirmation("Update 'test.setting' from old_value to new_value?", 'yes')
            ->expectsOutput("Setting 'test.setting' updated successfully.")
            ->assertExitCode(0);
    }

    public function test_handle_with_key_argument_only()
    {
        $setting = Setting::factory()->create([
            'key' => 'test.setting',
            'key_slug' => 'test_setting',
            'data_type' => SettingDataType::STRING,
            'value' => 'old_value',
        ]);

        Settings::shouldReceive('get')
            ->with('test.setting')
            ->once()
            ->andReturn('old_value');

        Settings::shouldReceive('set')
            ->with('test.setting', 'new_value')
            ->once();

        $this->artisan(SettingsSetCommand::class, ['key' => 'test.setting'])
            ->expectsQuestion("Enter new value for 'test.setting':", 'new_value')
            ->expectsConfirmation("Update 'test.setting' from old_value to new_value?", 'yes')
            ->expectsOutput("Setting 'test.setting' updated successfully.")
            ->assertExitCode(0);
    }

    public function test_handle_with_no_arguments()
    {
        Setting::factory()->create([
            'key' => 'test.setting',
            'key_slug' => 'test_setting',
            'data_type' => SettingDataType::STRING,
            'value' => 'old_value',
        ]);

        Settings::shouldReceive('get')
            ->with('test.setting')
            ->once()
            ->andReturn('old_value');

        Settings::shouldReceive('set')
            ->with('test.setting', 'new_value')
            ->once();

        $this->artisan(SettingsSetCommand::class)
            ->expectsQuestion('Enter setting key:', 'test.setting')
            ->expectsQuestion("Enter new value for 'test.setting':", 'new_value')
            ->expectsConfirmation("Update 'test.setting' from old_value to new_value?", 'yes')
            ->expectsOutput("Setting 'test.setting' updated successfully.")
            ->assertExitCode(0);
    }

    public function test_handle_with_nonexistent_setting()
    {
        $this->artisan(SettingsSetCommand::class, ['key' => 'nonexistent.setting'])
            ->expectsOutput("Setting 'nonexistent.setting' not found.")
            ->assertExitCode(1);
    }

    public function test_handle_with_cancelled_confirmation()
    {
        $setting = Setting::factory()->create([
            'key' => 'test.setting',
            'key_slug' => 'test_setting',
            'data_type' => SettingDataType::STRING,
            'value' => 'old_value',
        ]);

        Settings::shouldReceive('get')
            ->with('test.setting')
            ->once()
            ->andReturn('old_value');

        $this->artisan(SettingsSetCommand::class, [
            'key' => 'test.setting',
            'value' => 'new_value',
        ])
            ->expectsConfirmation("Update 'test.setting' from old_value to new_value?", 'no')
            ->expectsOutput('Operation cancelled.')
            ->assertExitCode(0);
    }

    public function test_parse_value_for_type_boolean()
    {
        $command = new SettingsSetCommand;
        $reflectionMethod = new \ReflectionMethod($command, 'parseValueForType');
        $reflectionMethod->setAccessible(true);

        $this->assertTrue($reflectionMethod->invoke($command, 'true', SettingDataType::BOOLEAN));
        $this->assertTrue($reflectionMethod->invoke($command, '1', SettingDataType::BOOLEAN));
        $this->assertFalse($reflectionMethod->invoke($command, 'false', SettingDataType::BOOLEAN));
        $this->assertFalse($reflectionMethod->invoke($command, '0', SettingDataType::BOOLEAN));
    }

    public function test_parse_value_for_type_integer()
    {
        $command = new SettingsSetCommand;
        $reflectionMethod = new \ReflectionMethod($command, 'parseValueForType');
        $reflectionMethod->setAccessible(true);

        $this->assertEquals(123, $reflectionMethod->invoke($command, '123', SettingDataType::INTEGER));
        $this->assertEquals(0, $reflectionMethod->invoke($command, '0', SettingDataType::INTEGER));
        $this->assertEquals(-456, $reflectionMethod->invoke($command, '-456', SettingDataType::INTEGER));
    }

    public function test_parse_value_for_type_float()
    {
        $command = new SettingsSetCommand;
        $reflectionMethod = new \ReflectionMethod($command, 'parseValueForType');
        $reflectionMethod->setAccessible(true);

        $this->assertEquals(123.45, $reflectionMethod->invoke($command, '123.45', SettingDataType::FLOAT));
        $this->assertEquals(0.0, $reflectionMethod->invoke($command, '0', SettingDataType::FLOAT));
        $this->assertEquals(-456.78, $reflectionMethod->invoke($command, '-456.78', SettingDataType::FLOAT));
    }

    public function test_parse_value_for_type_json()
    {
        $command = new SettingsSetCommand;
        $reflectionMethod = new \ReflectionMethod($command, 'parseValueForType');
        $reflectionMethod->setAccessible(true);

        $jsonString = '{"key": "value", "number": 123}';
        $expected = ['key' => 'value', 'number' => 123];
        $this->assertEquals($expected, $reflectionMethod->invoke($command, $jsonString, SettingDataType::JSON));
    }

    public function test_parse_value_for_type_string()
    {
        $command = new SettingsSetCommand;
        $reflectionMethod = new \ReflectionMethod($command, 'parseValueForType');
        $reflectionMethod->setAccessible(true);

        $this->assertEquals('test string', $reflectionMethod->invoke($command, 'test string', SettingDataType::STRING));
    }

    public function test_format_value()
    {
        $command = new SettingsSetCommand;
        $reflectionMethod = new \ReflectionMethod($command, 'formatValue');
        $reflectionMethod->setAccessible(true);

        $this->assertEquals('true', $reflectionMethod->invoke($command, true));
        $this->assertEquals('false', $reflectionMethod->invoke($command, false));
        $this->assertEquals('{"key":"value"}', $reflectionMethod->invoke($command, ['key' => 'value']));
        $this->assertEquals('test', $reflectionMethod->invoke($command, 'test'));
        $this->assertEquals('123', $reflectionMethod->invoke($command, 123));
        $this->assertEquals('123.45', $reflectionMethod->invoke($command, 123.45));
    }

    public function test_parse_value_for_type_array()
    {
        $command = new SettingsSetCommand;
        $reflectionMethod = new \ReflectionMethod($command, 'parseValueForType');
        $reflectionMethod->setAccessible(true);

        $jsonString = '["item1", "item2", "item3"]';
        $expected = ['item1', 'item2', 'item3'];
        $this->assertEquals($expected, $reflectionMethod->invoke($command, $jsonString, SettingDataType::ARRAY));
    }

    public function test_handle_with_boolean_setting()
    {
        $setting = Setting::factory()->create([
            'key' => 'test.boolean',
            'key_slug' => 'test_boolean',
            'data_type' => SettingDataType::BOOLEAN,
            'value' => 'false',
        ]);

        Settings::shouldReceive('get')
            ->with('test.boolean')
            ->once()
            ->andReturn(false);

        Settings::shouldReceive('set')
            ->with('test.boolean', true)
            ->once();

        $this->artisan(SettingsSetCommand::class, [
            'key' => 'test.boolean',
            'value' => 'true',
        ])
            ->expectsConfirmation("Update 'test.boolean' from false to true?", 'yes')
            ->expectsOutput("Setting 'test.boolean' updated successfully.")
            ->assertExitCode(0);
    }

    public function test_handle_with_integer_setting()
    {
        $setting = Setting::factory()->create([
            'key' => 'test.integer',
            'key_slug' => 'test_integer',
            'data_type' => SettingDataType::INTEGER,
            'value' => '100',
        ]);

        Settings::shouldReceive('get')
            ->with('test.integer')
            ->once()
            ->andReturn(100);

        Settings::shouldReceive('set')
            ->with('test.integer', 200)
            ->once();

        $this->artisan(SettingsSetCommand::class, [
            'key' => 'test.integer',
            'value' => '200',
        ])
            ->expectsConfirmation("Update 'test.integer' from 100 to 200?", 'yes')
            ->expectsOutput("Setting 'test.integer' updated successfully.")
            ->assertExitCode(0);
    }

    public function test_handle_with_float_setting()
    {
        $setting = Setting::factory()->create([
            'key' => 'test.float',
            'key_slug' => 'test_float',
            'data_type' => SettingDataType::FLOAT,
            'value' => '3.14',
        ]);

        Settings::shouldReceive('get')
            ->with('test.float')
            ->once()
            ->andReturn(3.14);

        Settings::shouldReceive('set')
            ->with('test.float', 2.71)
            ->once();

        $this->artisan(SettingsSetCommand::class, [
            'key' => 'test.float',
            'value' => '2.71',
        ])
            ->expectsConfirmation("Update 'test.float' from 3.14 to 2.71?", 'yes')
            ->expectsOutput("Setting 'test.float' updated successfully.")
            ->assertExitCode(0);
    }

    public function test_handle_with_json_setting()
    {
        $setting = Setting::factory()->create([
            'key' => 'test.json',
            'key_slug' => 'test_json',
            'data_type' => SettingDataType::JSON,
            'value' => '{"old": "value"}',
        ]);

        Settings::shouldReceive('get')
            ->with('test.json')
            ->once()
            ->andReturn(['old' => 'value']);

        Settings::shouldReceive('set')
            ->with('test.json', ['new' => 'value'])
            ->once();

        $this->artisan(SettingsSetCommand::class, [
            'key' => 'test.json',
            'value' => '{"new": "value"}',
        ])
            ->expectsConfirmation('Update \'test.json\' from {"old":"value"} to {"new":"value"}?', 'yes')
            ->expectsOutput("Setting 'test.json' updated successfully.")
            ->assertExitCode(0);
    }

    public function test_handle_with_array_setting()
    {
        $setting = Setting::factory()->create([
            'key' => 'test.array',
            'key_slug' => 'test_array',
            'data_type' => SettingDataType::ARRAY,
            'value' => '["old", "items"]',
        ]);

        Settings::shouldReceive('get')
            ->with('test.array')
            ->once()
            ->andReturn(['old', 'items']);

        Settings::shouldReceive('set')
            ->with('test.array', ['new', 'items'])
            ->once();

        $this->artisan(SettingsSetCommand::class, [
            'key' => 'test.array',
            'value' => '["new", "items"]',
        ])
            ->expectsConfirmation('Update \'test.array\' from ["old","items"] to ["new","items"]?', 'yes')
            ->expectsOutput("Setting 'test.array' updated successfully.")
            ->assertExitCode(0);
    }

    public function test_handle_with_default_value_suggestions_boolean()
    {
        $setting = Setting::factory()->create([
            'key' => 'test.boolean.default',
            'key_slug' => 'test_boolean_default',
            'data_type' => SettingDataType::BOOLEAN,
            'value' => 'true',
        ]);

        Settings::shouldReceive('get')
            ->with('test.boolean.default')
            ->once()
            ->andReturn(true);

        Settings::shouldReceive('set')
            ->with('test.boolean.default', false)
            ->once();

        $this->artisan(SettingsSetCommand::class, ['key' => 'test.boolean.default'])
            ->expectsQuestion("Enter new value for 'test.boolean.default':", 'false') // Default suggestion is 'true'
            ->expectsConfirmation("Update 'test.boolean.default' from true to false?", 'yes')
            ->expectsOutput("Setting 'test.boolean.default' updated successfully.")
            ->assertExitCode(0);
    }

    public function test_handle_with_default_value_suggestions_array()
    {
        $setting = Setting::factory()->create([
            'key' => 'test.array.default',
            'key_slug' => 'test_array_default',
            'data_type' => SettingDataType::ARRAY,
            'value' => '["existing", "values"]',
        ]);

        Settings::shouldReceive('get')
            ->with('test.array.default')
            ->once()
            ->andReturn(['existing', 'values']);

        Settings::shouldReceive('set')
            ->with('test.array.default', ['new', 'values'])
            ->once();

        $this->artisan(SettingsSetCommand::class, ['key' => 'test.array.default'])
            ->expectsQuestion("Enter new value for 'test.array.default':", '["new", "values"]') // Default suggestion is JSON encoded array
            ->expectsConfirmation('Update \'test.array.default\' from ["existing","values"] to ["new","values"]?', 'yes')
            ->expectsOutput("Setting 'test.array.default' updated successfully.")
            ->assertExitCode(0);
    }

    public function test_handle_with_settings_facade_exception()
    {
        $setting = Setting::factory()->create([
            'key' => 'test.exception',
            'key_slug' => 'test_exception',
            'data_type' => SettingDataType::STRING,
            'value' => 'old_value',
        ]);

        Settings::shouldReceive('get')
            ->with('test.exception')
            ->once()
            ->andReturn('old_value');

        Settings::shouldReceive('set')
            ->with('test.exception', 'new_value')
            ->once()
            ->andThrow(new \Exception('Database connection failed'));

        $this->artisan(SettingsSetCommand::class, [
            'key' => 'test.exception',
            'value' => 'new_value',
        ])
            ->expectsConfirmation("Update 'test.exception' from old_value to new_value?", 'yes')
            ->expectsOutput('Error: Database connection failed')
            ->assertExitCode(1);
    }

    public function test_handle_with_key_anticipation()
    {
        Setting::factory()->create([
            'key' => 'available.setting1',
            'key_slug' => 'available_setting1',
            'data_type' => SettingDataType::STRING,
            'value' => 'value1',
        ]);

        Setting::factory()->create([
            'key' => 'available.setting2',
            'key_slug' => 'available_setting2',
            'data_type' => SettingDataType::STRING,
            'value' => 'value2',
        ]);

        Settings::shouldReceive('get')
            ->with('available.setting1')
            ->once()
            ->andReturn('value1');

        Settings::shouldReceive('set')
            ->with('available.setting1', 'new_value')
            ->once();

        $this->artisan(SettingsSetCommand::class)
            ->expectsQuestion('Enter setting key:', 'available.setting1') // Anticipate should suggest existing keys
            ->expectsQuestion("Enter new value for 'available.setting1':", 'new_value')
            ->expectsConfirmation("Update 'available.setting1' from value1 to new_value?", 'yes')
            ->expectsOutput("Setting 'available.setting1' updated successfully.")
            ->assertExitCode(0);
    }

    public function test_parse_value_for_type_json_invalid()
    {
        $command = new SettingsSetCommand;
        $reflectionMethod = new \ReflectionMethod($command, 'parseValueForType');
        $reflectionMethod->setAccessible(true);

        // Test invalid JSON - should return null when json_decode fails
        $invalidJsonString = '{invalid json}';
        $result = $reflectionMethod->invoke($command, $invalidJsonString, SettingDataType::JSON);
        $this->assertNull($result);
    }

    public function test_handle_with_parsing_exception()
    {
        $setting = Setting::factory()->create([
            'key' => 'test.json.invalid',
            'key_slug' => 'test_json_invalid',
            'data_type' => SettingDataType::JSON,
            'value' => '{"valid": "json"}',
        ]);

        Settings::shouldReceive('get')
            ->with('test.json.invalid')
            ->once()
            ->andReturn(['valid' => 'json']);

        // This should not throw exception in parseValueForType as it handles invalid JSON gracefully
        // But let's test what happens when we set null value
        Settings::shouldReceive('set')
            ->with('test.json.invalid', null)
            ->once();

        $this->artisan(SettingsSetCommand::class, [
            'key' => 'test.json.invalid',
            'value' => '{invalid json}',
        ])
            ->expectsConfirmation('Update \'test.json.invalid\' from {"valid":"json"} to ?', 'yes')
            ->expectsOutput("Setting 'test.json.invalid' updated successfully.")
            ->assertExitCode(0);
    }
}
