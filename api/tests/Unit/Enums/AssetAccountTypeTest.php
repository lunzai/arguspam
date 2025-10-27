<?php

namespace Tests\Unit\Enums;

use App\Enums\AssetAccountType;
use PHPUnit\Framework\TestCase;

class AssetAccountTypeTest extends TestCase
{
    public function test_enum_cases_exist(): void
    {
        $cases = AssetAccountType::cases();
        $this->assertCount(2, $cases);

        $this->assertContains(AssetAccountType::ADMIN, $cases);
        $this->assertContains(AssetAccountType::JIT, $cases);
    }

    public function test_enum_values(): void
    {
        $this->assertEquals('admin', AssetAccountType::ADMIN->value);
        $this->assertEquals('jit', AssetAccountType::JIT->value);
    }

    public function test_enum_from_string(): void
    {
        $this->assertEquals(AssetAccountType::ADMIN, AssetAccountType::from('admin'));
        $this->assertEquals(AssetAccountType::JIT, AssetAccountType::from('jit'));
    }

    public function test_enum_try_from_string(): void
    {
        $this->assertEquals(AssetAccountType::ADMIN, AssetAccountType::tryFrom('admin'));
        $this->assertEquals(AssetAccountType::JIT, AssetAccountType::tryFrom('jit'));
        $this->assertNull(AssetAccountType::tryFrom('invalid'));
        $this->assertNull(AssetAccountType::tryFrom(''));
        $this->assertNull(AssetAccountType::tryFrom(null));
    }

    public function test_enum_from_invalid_string_throws_exception(): void
    {
        $this->expectException(\ValueError::class);
        AssetAccountType::from('invalid');
    }

    public function test_enum_from_empty_string_throws_exception(): void
    {
        $this->expectException(\ValueError::class);
        AssetAccountType::from('');
    }

    public function test_enum_from_null_throws_exception(): void
    {
        $this->expectException(\ValueError::class);
        AssetAccountType::from(null);
    }

    public function test_enum_is_backed_enum(): void
    {
        $this->assertTrue(AssetAccountType::ADMIN instanceof \BackedEnum);
        $this->assertTrue(AssetAccountType::JIT instanceof \BackedEnum);
    }

    public function test_enum_implements_unit_enum(): void
    {
        $this->assertTrue(AssetAccountType::ADMIN instanceof \UnitEnum);
        $this->assertTrue(AssetAccountType::JIT instanceof \UnitEnum);
    }

    public function test_enum_name_property(): void
    {
        $this->assertEquals('ADMIN', AssetAccountType::ADMIN->name);
        $this->assertEquals('JIT', AssetAccountType::JIT->name);
    }

    public function test_enum_to_string(): void
    {
        $this->assertEquals('admin', AssetAccountType::ADMIN->value);
        $this->assertEquals('jit', AssetAccountType::JIT->value);
    }

    public function test_enum_serialization(): void
    {
        $admin = AssetAccountType::ADMIN;
        $jit = AssetAccountType::JIT;

        // Test serialization
        $serializedAdmin = serialize($admin);
        $serializedJit = serialize($jit);

        $this->assertIsString($serializedAdmin);
        $this->assertIsString($serializedJit);

        // Test deserialization
        $unserializedAdmin = unserialize($serializedAdmin);
        $unserializedJit = unserialize($serializedJit);

        $this->assertEquals($admin, $unserializedAdmin);
        $this->assertEquals($jit, $unserializedJit);
    }

    public function test_enum_json_serialization(): void
    {
        $admin = AssetAccountType::ADMIN;
        $jit = AssetAccountType::JIT;

        $this->assertEquals('"admin"', json_encode($admin));
        $this->assertEquals('"jit"', json_encode($jit));
    }

    public function test_enum_equality(): void
    {
        $admin1 = AssetAccountType::ADMIN;
        $admin2 = AssetAccountType::ADMIN;
        $jit = AssetAccountType::JIT;

        $this->assertTrue($admin1 === $admin2);
        $this->assertTrue($admin1 == $admin2);
        $this->assertFalse($admin1 === $jit);
        $this->assertFalse($admin1 == $jit);
    }

    public function test_enum_switch_statement(): void
    {
        $admin = AssetAccountType::ADMIN;
        $jit = AssetAccountType::JIT;

        $adminResult = match ($admin) {
            AssetAccountType::ADMIN => 'admin_account',
            AssetAccountType::JIT => 'jit_account',
        };

        $jitResult = match ($jit) {
            AssetAccountType::ADMIN => 'admin_account',
            AssetAccountType::JIT => 'jit_account',
        };

        $this->assertEquals('admin_account', $adminResult);
        $this->assertEquals('jit_account', $jitResult);
    }

    public function test_enum_in_array(): void
    {
        $validTypes = [AssetAccountType::ADMIN, AssetAccountType::JIT];

        $this->assertTrue(in_array(AssetAccountType::ADMIN, $validTypes));
        $this->assertTrue(in_array(AssetAccountType::JIT, $validTypes));
    }

    public function test_enum_array_values(): void
    {
        $values = array_column(AssetAccountType::cases(), 'value');

        $this->assertContains('admin', $values);
        $this->assertContains('jit', $values);
        $this->assertCount(2, $values);
    }

    public function test_enum_array_keys(): void
    {
        $keys = array_column(AssetAccountType::cases(), 'name');

        $this->assertContains('ADMIN', $keys);
        $this->assertContains('JIT', $keys);
        $this->assertCount(2, $keys);
    }

    public function test_enum_foreach_iteration(): void
    {
        $foundAdmin = false;
        $foundJit = false;

        foreach (AssetAccountType::cases() as $case) {
            if ($case === AssetAccountType::ADMIN) {
                $foundAdmin = true;
            }
            if ($case === AssetAccountType::JIT) {
                $foundJit = true;
            }
        }

        $this->assertTrue($foundAdmin);
        $this->assertTrue($foundJit);
    }

    public function test_enum_switch_with_default(): void
    {
        $admin = AssetAccountType::ADMIN;
        $jit = AssetAccountType::JIT;

        $adminResult = match ($admin) {
            AssetAccountType::ADMIN => 'admin_type',
            AssetAccountType::JIT => 'jit_type',
            default => 'unknown_type',
        };

        $jitResult = match ($jit) {
            AssetAccountType::ADMIN => 'admin_type',
            AssetAccountType::JIT => 'jit_type',
            default => 'unknown_type',
        };

        $this->assertEquals('admin_type', $adminResult);
        $this->assertEquals('jit_type', $jitResult);
    }

    public function test_enum_case_insensitive_from(): void
    {
        // Test that from() is case sensitive
        $this->expectException(\ValueError::class);
        AssetAccountType::from('ADMIN');
    }

    public function test_enum_case_insensitive_try_from(): void
    {
        // Test that tryFrom() is case sensitive
        $this->assertNull(AssetAccountType::tryFrom('ADMIN'));
        $this->assertNull(AssetAccountType::tryFrom('JIT'));
    }

    public function test_enum_whitespace_handling(): void
    {
        $this->expectException(\ValueError::class);
        AssetAccountType::from(' admin ');
    }

    public function test_enum_numeric_strings(): void
    {
        $this->assertNull(AssetAccountType::tryFrom('1'));
        $this->assertNull(AssetAccountType::tryFrom('0'));
    }

    public function test_enum_boolean_strings(): void
    {
        $this->assertNull(AssetAccountType::tryFrom('true'));
        $this->assertNull(AssetAccountType::tryFrom('false'));
    }
}
