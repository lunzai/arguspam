<?php

namespace Tests\Unit\Models;

use App\Enums\SettingDataType;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    private Setting $setting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setting = Setting::factory()->create([
            'key' => 'test_key',
            'value' => 'test_value',
            'data_type' => SettingDataType::STRING,
        ]);
    }

    public function test_setting_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'key',
            'key_slug',
            'value',
            'group',
            'label',
            'description',
            'data_type',
        ];

        $this->assertEquals($expectedFillable, $this->setting->getFillable());
    }

    public function test_setting_has_correct_casts(): void
    {
        $casts = $this->setting->getCasts();

        $this->assertArrayHasKey('data_type', $casts);
        $this->assertEquals(SettingDataType::class, $casts['data_type']);
    }

    public function test_get_typed_value_attribute_returns_cast_value(): void
    {
        // Test string type
        $this->setting->update([
            'value' => '123',
            'data_type' => SettingDataType::STRING,
        ]);
        $this->assertEquals('123', $this->setting->fresh()->typed_value);

        // Test integer type
        $this->setting->update([
            'value' => '123',
            'data_type' => SettingDataType::INTEGER,
        ]);
        $this->assertEquals(123, $this->setting->fresh()->typed_value);

        // Test float type
        $this->setting->update([
            'value' => '123.45',
            'data_type' => SettingDataType::FLOAT,
        ]);
        $this->assertEquals(123.45, $this->setting->fresh()->typed_value);

        // Test boolean type
        $this->setting->update([
            'value' => 'true',
            'data_type' => SettingDataType::BOOLEAN,
        ]);
        $this->assertTrue($this->setting->fresh()->typed_value);

        // Test JSON type
        $this->setting->update([
            'value' => '{"key": "value"}',
            'data_type' => SettingDataType::JSON,
        ]);
        $this->assertEquals(['key' => 'value'], $this->setting->fresh()->typed_value);

        // Test array type
        $this->setting->update([
            'value' => 'item1,item2,item3',
            'data_type' => SettingDataType::ARRAY,
        ]);
        $this->assertEquals(['item1', 'item2', 'item3'], $this->setting->fresh()->typed_value);
    }

    public function test_set_typed_value_attribute_with_valid_values(): void
    {
        // Test string type
        $this->setting->data_type = SettingDataType::STRING;
        $this->setting->typed_value = 'test string';
        $this->assertEquals('test string', $this->setting->value);

        // Test integer type
        $this->setting->data_type = SettingDataType::INTEGER;
        $this->setting->typed_value = 42;
        $this->assertEquals('42', $this->setting->value);

        // Test float type
        $this->setting->data_type = SettingDataType::FLOAT;
        $this->setting->typed_value = 3.14;
        $this->assertEquals('3.14', $this->setting->value);

        // Test boolean type
        $this->setting->data_type = SettingDataType::BOOLEAN;
        $this->setting->typed_value = true;
        $this->assertEquals('true', $this->setting->value);

        $this->setting->typed_value = false;
        $this->assertEquals('false', $this->setting->value);

        // Test JSON type
        $this->setting->data_type = SettingDataType::JSON;
        $this->setting->typed_value = ['key' => 'value'];
        $this->assertEquals('{"key":"value"}', $this->setting->value);

        // Test array type
        $this->setting->data_type = SettingDataType::ARRAY;
        $this->setting->typed_value = ['item1', 'item2'];
        $this->assertEquals('["item1","item2"]', $this->setting->value);
    }

    public function test_set_typed_value_attribute_throws_exception_for_invalid_values(): void
    {
        // Test line 40: throw new \InvalidArgumentException for invalid values
        $this->setting->data_type = SettingDataType::INTEGER;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value for type integer');

        // Array should trigger validation failure for integer
        $this->setting->typed_value = ['not', 'an', 'integer'];
    }

    public function test_set_typed_value_attribute_throws_exception_for_invalid_json(): void
    {
        // Test another case that triggers line 40
        $this->setting->data_type = SettingDataType::JSON;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid value for type json');

        // Invalid JSON should trigger validation failure
        $this->setting->typed_value = 'invalid json string';
    }


    public function test_setting_uses_correct_traits(): void
    {
        $traits = class_uses_recursive(Setting::class);

        $this->assertContains('Illuminate\\Database\\Eloquent\\Factories\\HasFactory', $traits);
    }

    public function test_setting_extends_base_model(): void
    {
        $this->assertInstanceOf(\App\Models\Model::class, $this->setting);
    }

    public function test_setting_creation_with_all_attributes(): void
    {
        $setting = Setting::factory()->create([
            'key' => 'test.setting',
            'key_slug' => 'test_setting',
            'value' => 'test_value',
            'group' => 'test_group',
            'label' => 'Test Setting',
            'description' => 'A test setting for unit tests',
            'data_type' => SettingDataType::STRING,
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'test.setting',
            'key_slug' => 'test_setting',
            'value' => 'test_value',
            'group' => 'test_group',
            'label' => 'Test Setting',
            'description' => 'A test setting for unit tests',
            'data_type' => SettingDataType::STRING->value,
        ]);
    }

    public function test_data_type_cast_works_correctly(): void
    {
        $setting = Setting::factory()->create([
            'data_type' => SettingDataType::INTEGER,
        ]);

        // Verify the enum is cast correctly
        $this->assertInstanceOf(SettingDataType::class, $setting->data_type);
        $this->assertEquals(SettingDataType::INTEGER, $setting->data_type);
    }
}