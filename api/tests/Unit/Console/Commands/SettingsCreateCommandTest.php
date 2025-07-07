<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\SettingsCreateCommand;
use App\Enums\SettingDataType;
use App\Facades\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsCreateCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_with_all_arguments_and_options()
    {
        Settings::shouldReceive('has')
            ->with('test.setting')
            ->once()
            ->andReturn(false);

        Settings::shouldReceive('create')
            ->with([
                'key' => 'test.setting',
                'value' => 'test_value',
                'data_type' => SettingDataType::STRING,
                'group' => 'test_group',
                'label' => 'Test Label',
                'description' => 'Test Description',
            ])
            ->once();

        $this->artisan(SettingsCreateCommand::class, [
            'key' => 'test.setting',
            '--type' => 'string',
            '--group' => 'test_group',
            '--label' => 'Test Label',
            '--description' => 'Test Description',
        ])
            ->expectsQuestion('Enter string value:', 'test_value')
            ->expectsOutput("Setting 'test.setting' created successfully.")
            ->assertExitCode(0);
    }

    public function test_handle_with_existing_setting()
    {
        Settings::shouldReceive('has')
            ->with('existing.setting')
            ->once()
            ->andReturn(true);

        $this->artisan(SettingsCreateCommand::class, ['key' => 'existing.setting'])
            ->expectsOutput("Setting 'existing.setting' already exists.")
            ->assertExitCode(1);
    }

    public function test_handle_with_no_key_argument()
    {
        Settings::shouldReceive('has')
            ->with('asked.key')
            ->once()
            ->andReturn(false);

        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['existing_group']);

        Settings::shouldReceive('create')
            ->once();

        $this->artisan(SettingsCreateCommand::class)
            ->expectsQuestion('Enter setting key (e.g., app.name):', 'asked.key')
            ->expectsChoice('Select data type:', 'string', ['string', 'integer', 'float', 'boolean', 'json', 'array'])
            ->expectsChoice('Select group or enter a new one:', 'existing_group', ['existing_group', '[new group]'])
            ->expectsQuestion('Enter display label:', 'asked.key')
            ->expectsQuestion('Enter description (optional):', 'Test description')
            ->expectsQuestion('Enter string value:', 'test_value')
            ->expectsOutput("Setting 'asked.key' created successfully.")
            ->assertExitCode(0);
    }

    public function test_handle_with_invalid_data_type()
    {
        Settings::shouldReceive('has')
            ->with('test.setting')
            ->once()
            ->andReturn(false);

        $this->artisan(SettingsCreateCommand::class, [
            'key' => 'test.setting',
            '--type' => 'invalid_type',
        ])
            ->expectsOutput("Invalid data type 'invalid_type'.")
            ->assertExitCode(1);
    }

    public function test_handle_with_new_group()
    {
        Settings::shouldReceive('has')
            ->with('test.setting')
            ->once()
            ->andReturn(false);

        Settings::shouldReceive('groups')
            ->once()
            ->andReturn(['existing_group']);

        Settings::shouldReceive('create')
            ->once();

        $this->artisan(SettingsCreateCommand::class, [
            'key' => 'test.setting',
            '--type' => 'string',
        ])
            ->expectsChoice('Select group or enter a new one:', '[new group]', ['existing_group', '[new group]'])
            ->expectsQuestion('Enter new group name:', 'new_group')
            ->expectsQuestion('Enter display label:', 'test.setting')
            ->expectsQuestion('Enter description (optional):', '')
            ->expectsQuestion('Enter string value:', 'test_value')
            ->expectsOutput("Setting 'test.setting' created successfully.")
            ->assertExitCode(0);
    }

    public function test_handle_with_no_existing_groups()
    {
        Settings::shouldReceive('has')
            ->with('test.setting')
            ->once()
            ->andReturn(false);

        Settings::shouldReceive('groups')
            ->once()
            ->andReturn([]);

        Settings::shouldReceive('create')
            ->once();

        $this->artisan(SettingsCreateCommand::class, [
            'key' => 'test.setting',
            '--type' => 'string',
        ])
            ->expectsQuestion('Enter group name:', 'first_group')
            ->expectsQuestion('Enter display label:', 'test.setting')
            ->expectsQuestion('Enter description (optional):', '')
            ->expectsQuestion('Enter string value:', 'test_value')
            ->expectsOutput("Setting 'test.setting' created successfully.")
            ->assertExitCode(0);
    }

    public function test_ask_for_typed_value_boolean()
    {
        Settings::shouldReceive('has')
            ->with('test.boolean')
            ->once()
            ->andReturn(false);

        Settings::shouldReceive('create')
            ->with([
                'key' => 'test.boolean',
                'value' => true,
                'data_type' => SettingDataType::BOOLEAN,
                'group' => 'test_group',
                'label' => 'Test Boolean',
                'description' => 'Test Description',
            ])
            ->once();

        $this->artisan(SettingsCreateCommand::class, [
            'key' => 'test.boolean',
            '--type' => 'boolean',
            '--group' => 'test_group',
            '--label' => 'Test Boolean',
            '--description' => 'Test Description',
        ])
            ->expectsConfirmation('Enter value (yes/no):', 'yes')
            ->expectsOutput("Setting 'test.boolean' created successfully.")
            ->assertExitCode(0);
    }

    public function test_ask_for_typed_value_integer()
    {
        Settings::shouldReceive('has')
            ->with('test.integer')
            ->once()
            ->andReturn(false);

        Settings::shouldReceive('create')
            ->with([
                'key' => 'test.integer',
                'value' => 42,
                'data_type' => SettingDataType::INTEGER,
                'group' => 'test_group',
                'label' => 'Test Integer',
                'description' => 'Test Description',
            ])
            ->once();

        $this->artisan(SettingsCreateCommand::class, [
            'key' => 'test.integer',
            '--type' => 'integer',
            '--group' => 'test_group',
            '--label' => 'Test Integer',
            '--description' => 'Test Description',
        ])
            ->expectsQuestion('Enter integer value:', '42')
            ->expectsOutput("Setting 'test.integer' created successfully.")
            ->assertExitCode(0);
    }

    public function test_ask_for_typed_value_float()
    {
        Settings::shouldReceive('has')
            ->with('test.float')
            ->once()
            ->andReturn(false);

        Settings::shouldReceive('create')
            ->with([
                'key' => 'test.float',
                'value' => 3.14,
                'data_type' => SettingDataType::FLOAT,
                'group' => 'test_group',
                'label' => 'Test Float',
                'description' => 'Test Description',
            ])
            ->once();

        $this->artisan(SettingsCreateCommand::class, [
            'key' => 'test.float',
            '--type' => 'float',
            '--group' => 'test_group',
            '--label' => 'Test Float',
            '--description' => 'Test Description',
        ])
            ->expectsQuestion('Enter float value:', '3.14')
            ->expectsOutput("Setting 'test.float' created successfully.")
            ->assertExitCode(0);
    }

    public function test_ask_json_with_valid_json()
    {
        Settings::shouldReceive('has')
            ->with('test.json')
            ->once()
            ->andReturn(false);

        Settings::shouldReceive('create')
            ->with([
                'key' => 'test.json',
                'value' => ['name' => 'test', 'value' => 123],
                'data_type' => SettingDataType::JSON,
                'group' => 'test_group',
                'label' => 'Test JSON',
                'description' => 'Test Description',
            ])
            ->once();

        $this->artisan(SettingsCreateCommand::class, [
            'key' => 'test.json',
            '--type' => 'json',
            '--group' => 'test_group',
            '--label' => 'Test JSON',
            '--description' => 'Test Description',
        ])
            ->expectsOutput('Enter JSON value (leave empty and press enter to finish):')
            ->expectsQuestion('> ', '{"name": "test",')
            ->expectsQuestion('> ', '"value": 123}')
            ->expectsQuestion('> ', '')
            ->expectsOutput("Setting 'test.json' created successfully.")
            ->assertExitCode(0);
    }

    public function test_ask_json_with_invalid_json()
    {
        Settings::shouldReceive('has')
            ->with('test.json.invalid')
            ->once()
            ->andReturn(false);

        Settings::shouldReceive('create')
            ->with([
                'key' => 'test.json.invalid',
                'value' => [], // Invalid JSON results in empty array
                'data_type' => SettingDataType::JSON,
                'group' => 'test_group',
                'label' => 'Test JSON Invalid',
                'description' => 'Test Description',
            ])
            ->once();

        $this->artisan(SettingsCreateCommand::class, [
            'key' => 'test.json.invalid',
            '--type' => 'json',
            '--group' => 'test_group',
            '--label' => 'Test JSON Invalid',
            '--description' => 'Test Description',
        ])
            ->expectsOutput('Enter JSON value (leave empty and press enter to finish):')
            ->expectsQuestion('> ', '{invalid json')
            ->expectsQuestion('> ', '')
            ->expectsOutput('Invalid JSON. Using empty object instead.')
            ->expectsOutput("Setting 'test.json.invalid' created successfully.")
            ->assertExitCode(0);
    }

    public function test_ask_array()
    {
        Settings::shouldReceive('has')
            ->with('test.array')
            ->once()
            ->andReturn(false);

        Settings::shouldReceive('create')
            ->with([
                'key' => 'test.array',
                'value' => ['item1', 'item2', 'item3'],
                'data_type' => SettingDataType::ARRAY,
                'group' => 'test_group',
                'label' => 'Test Array',
                'description' => 'Test Description',
            ])
            ->once();

        $this->artisan(SettingsCreateCommand::class, [
            'key' => 'test.array',
            '--type' => 'array',
            '--group' => 'test_group',
            '--label' => 'Test Array',
            '--description' => 'Test Description',
        ])
            ->expectsOutput('Enter array values (one per line, leave empty and press enter to finish):')
            ->expectsQuestion('> ', 'item1')
            ->expectsQuestion('> ', 'item2')
            ->expectsQuestion('> ', 'item3')
            ->expectsQuestion('> ', '')
            ->expectsOutput("Setting 'test.array' created successfully.")
            ->assertExitCode(0);
    }

    public function test_handle_with_creation_exception()
    {
        Settings::shouldReceive('has')
            ->with('test.setting')
            ->once()
            ->andReturn(false);

        Settings::shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('Database error'));

        $this->artisan(SettingsCreateCommand::class, [
            'key' => 'test.setting',
            '--type' => 'string',
            '--group' => 'test_group',
            '--label' => 'Test Label',
            '--description' => 'Test Description',
        ])
            ->expectsQuestion('Enter string value:', 'test_value')
            ->expectsOutput('Error: Database error')
            ->assertExitCode(1);
    }

    public function test_command_signature()
    {
        $command = new SettingsCreateCommand;
        $this->assertStringContainsString('settings:create', $command->getName());
        $this->assertTrue($command->getDefinition()->hasOption('type'));
        $this->assertTrue($command->getDefinition()->hasOption('group'));
        $this->assertTrue($command->getDefinition()->hasOption('label'));
        $this->assertTrue($command->getDefinition()->hasOption('description'));
    }

    public function test_command_description()
    {
        $command = new SettingsCreateCommand;
        $this->assertEquals('Create a new setting', $command->getDescription());
    }
}
