<?php

namespace Tests\Unit\Enums;

use App\Enums\SettingDataType;
use PHPUnit\Framework\TestCase;

class SettingDataTypeTest extends TestCase
{
    public function test_cast_string_values(): void
    {
        $type = SettingDataType::STRING;

        $this->assertEquals('hello', $type->cast('hello'));
        $this->assertEquals('123', $type->cast('123'));
        $this->assertEquals('0', $type->cast('0'));
        $this->assertEquals('', $type->cast(''));
    }

    public function test_cast_integer_values(): void
    {
        $type = SettingDataType::INTEGER;

        $this->assertEquals(123, $type->cast('123'));
        $this->assertEquals(0, $type->cast('0'));
        $this->assertEquals(-456, $type->cast('-456'));
        $this->assertEquals(0, $type->cast('abc')); // Invalid strings become 0
    }

    public function test_cast_float_values(): void
    {
        $type = SettingDataType::FLOAT;

        $this->assertEquals(123.45, $type->cast('123.45'));
        $this->assertEquals(0.0, $type->cast('0'));
        $this->assertEquals(-456.78, $type->cast('-456.78'));
        $this->assertEquals(123.0, $type->cast('123'));
    }

    public function test_cast_boolean_values(): void
    {
        $type = SettingDataType::BOOLEAN;

        $this->assertTrue($type->cast('true'));
        $this->assertTrue($type->cast('1'));
        $this->assertTrue($type->cast('on'));
        $this->assertTrue($type->cast('yes'));

        $this->assertFalse($type->cast('false'));
        $this->assertFalse($type->cast('0'));
        $this->assertFalse($type->cast('off'));
        $this->assertFalse($type->cast('no'));
        $this->assertFalse($type->cast(''));
    }

    public function test_cast_json_values(): void
    {
        $type = SettingDataType::JSON;

        $this->assertEquals(['key' => 'value'], $type->cast('{"key":"value"}'));
        $this->assertEquals([1, 2, 3], $type->cast('[1,2,3]'));
        $this->assertEquals('string', $type->cast('"string"'));
        $this->assertNull($type->cast('invalid json'));
    }

    public function test_cast_array_values(): void
    {
        $type = SettingDataType::ARRAY;

        // String input gets exploded (cast method expects string)
        $this->assertEquals(['a', 'b', 'c'], $type->cast('a,b,c'));
        $this->assertEquals(['single'], $type->cast('single'));
        $this->assertEquals([''], $type->cast(''));
    }

    public function test_validate_string_values(): void
    {
        $type = SettingDataType::STRING;

        $this->assertTrue($type->validate('hello'));
        $this->assertTrue($type->validate(''));
        $this->assertTrue($type->validate('123'));
        $this->assertTrue($type->validate(123));
        $this->assertTrue($type->validate(123.45));
        $this->assertFalse($type->validate([]));
        $this->assertFalse($type->validate(null));
    }

    public function test_validate_integer_values(): void
    {
        $type = SettingDataType::INTEGER;

        $this->assertTrue($type->validate(123));
        $this->assertTrue($type->validate('123'));
        $this->assertTrue($type->validate('0'));
        $this->assertTrue($type->validate(0));
        $this->assertFalse($type->validate('123.45'));
        $this->assertFalse($type->validate('abc'));
        $this->assertFalse($type->validate(''));
        $this->assertFalse($type->validate([]));
    }

    public function test_validate_float_values(): void
    {
        $type = SettingDataType::FLOAT;

        $this->assertTrue($type->validate(123.45));
        $this->assertTrue($type->validate(123));
        $this->assertTrue($type->validate('123.45'));
        $this->assertTrue($type->validate('123'));
        $this->assertTrue($type->validate('0'));
        $this->assertFalse($type->validate('abc'));
        $this->assertFalse($type->validate(''));
        $this->assertFalse($type->validate([]));
    }

    public function test_validate_boolean_values(): void
    {
        $type = SettingDataType::BOOLEAN;

        $this->assertTrue($type->validate(true));
        $this->assertTrue($type->validate(false));
        $this->assertTrue($type->validate('true'));
        $this->assertTrue($type->validate('false'));
        $this->assertTrue($type->validate('TRUE'));
        $this->assertTrue($type->validate('FALSE'));
        $this->assertTrue($type->validate('1'));
        $this->assertTrue($type->validate('0'));
        $this->assertTrue($type->validate(1));
        $this->assertTrue($type->validate(0));
        $this->assertFalse($type->validate('maybe'));
        $this->assertFalse($type->validate(''));
        $this->assertFalse($type->validate([]));
    }

    public function test_validate_json_values(): void
    {
        $type = SettingDataType::JSON;

        $this->assertTrue($type->validate(['key' => 'value']));
        $this->assertTrue($type->validate('{"key":"value"}'));
        $this->assertTrue($type->validate('[1,2,3]'));
        $this->assertTrue($type->validate('"string"'));
        $this->assertTrue($type->validate('true'));
        $this->assertFalse($type->validate('invalid json'));
        $this->assertFalse($type->validate('{invalid}'));
    }

    public function test_validate_array_values(): void
    {
        $type = SettingDataType::ARRAY;

        $this->assertTrue($type->validate(['a', 'b', 'c']));
        $this->assertTrue($type->validate('a,b,c'));
        $this->assertTrue($type->validate('single'));
        $this->assertTrue($type->validate(''));
        $this->assertFalse($type->validate(123));
        $this->assertFalse($type->validate(true));
        $this->assertFalse($type->validate(null));
    }

    public function test_prepare_string_values(): void
    {
        $type = SettingDataType::STRING;

        $this->assertEquals('hello', $type->prepare('hello'));
        $this->assertEquals('123', $type->prepare(123));
        $this->assertEquals('123.45', $type->prepare(123.45));
        $this->assertEquals('1', $type->prepare(true));
        $this->assertEquals('', $type->prepare(false));
    }

    public function test_prepare_integer_values(): void
    {
        $type = SettingDataType::INTEGER;

        $this->assertEquals('123', $type->prepare(123));
        $this->assertEquals('123', $type->prepare('123'));
        $this->assertEquals('123', $type->prepare(123.99)); // Truncated
        $this->assertEquals('1', $type->prepare(true));
        $this->assertEquals('0', $type->prepare(false));
    }

    public function test_prepare_float_values(): void
    {
        $type = SettingDataType::FLOAT;

        $this->assertEquals('123.45', $type->prepare(123.45));
        $this->assertEquals('123', $type->prepare(123));
        $this->assertEquals('123.45', $type->prepare('123.45'));
        $this->assertEquals('1', $type->prepare(true));
        $this->assertEquals('0', $type->prepare(false));
    }

    public function test_prepare_boolean_values(): void
    {
        $type = SettingDataType::BOOLEAN;

        $this->assertEquals('true', $type->prepare(true));
        $this->assertEquals('false', $type->prepare(false));
        $this->assertEquals('true', $type->prepare(1));
        $this->assertEquals('false', $type->prepare(0));
        $this->assertEquals('true', $type->prepare('anything'));
        $this->assertEquals('false', $type->prepare(''));
    }

    public function test_prepare_json_values(): void
    {
        $type = SettingDataType::JSON;

        $this->assertEquals('{"key":"value"}', $type->prepare(['key' => 'value']));
        $this->assertEquals('[1,2,3]', $type->prepare([1, 2, 3]));
        $this->assertEquals('{"key":"value"}', $type->prepare('{"key":"value"}'));
        $this->assertEquals('string', $type->prepare('string')); // String stays as string
    }

    public function test_prepare_array_values(): void
    {
        $type = SettingDataType::ARRAY;

        $this->assertEquals('["a","b","c"]', $type->prepare(['a', 'b', 'c']));
        $this->assertEquals('[1,2,3]', $type->prepare([1, 2, 3]));
        $this->assertEquals('a,b,c', $type->prepare('a,b,c'));
        $this->assertEquals('single', $type->prepare('single'));
    }

    public function test_all_enum_values_exist(): void
    {
        $expectedValues = ['string', 'integer', 'float', 'boolean', 'json', 'array'];
        $actualValues = array_map(fn ($case) => $case->value, SettingDataType::cases());

        $this->assertEquals($expectedValues, $actualValues);
    }

    public function test_enum_from_string(): void
    {
        $this->assertEquals(SettingDataType::STRING, SettingDataType::from('string'));
        $this->assertEquals(SettingDataType::INTEGER, SettingDataType::from('integer'));
        $this->assertEquals(SettingDataType::FLOAT, SettingDataType::from('float'));
        $this->assertEquals(SettingDataType::BOOLEAN, SettingDataType::from('boolean'));
        $this->assertEquals(SettingDataType::JSON, SettingDataType::from('json'));
        $this->assertEquals(SettingDataType::ARRAY, SettingDataType::from('array'));
    }

    public function test_enum_try_from_invalid_string(): void
    {
        $this->assertNull(SettingDataType::tryFrom('invalid'));
        $this->assertNull(SettingDataType::tryFrom(''));
        $this->assertNull(SettingDataType::tryFrom('STRING'));
    }
}
