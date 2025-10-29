<?php

namespace Tests\Unit\Enums;

use App\Enums\AccessRestrictionType;
use App\Enums\AssetAccessRole;
use App\Enums\AssetAccountType;
use App\Enums\AuditAction;
use App\Enums\CacheKey;
use App\Enums\DatabaseScope;
use App\Enums\Dbms;
use App\Enums\RequestStatus;
use App\Enums\RiskRating;
use App\Enums\SessionFlag;
use App\Enums\SessionStatus;
use App\Enums\Status;
use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class AllEnumsTest extends TestCase
{
    public function test_all_enums_are_backed_enums(): void
    {
        $enums = [
            AccessRestrictionType::class,
            AssetAccessRole::class,
            AssetAccountType::class,
            AuditAction::class,
            CacheKey::class,
            Dbms::class,
            DatabaseScope::class,
            RequestStatus::class,
            RiskRating::class,
            SessionFlag::class,
            SessionStatus::class,
            Status::class,
            UserRole::class,
        ];

        foreach ($enums as $enumClass) {
            $cases = $enumClass::cases();
            $this->assertNotEmpty($cases, "Enum {$enumClass} should have cases");

            foreach ($cases as $case) {
                $this->assertTrue($case instanceof \BackedEnum, "Enum case in {$enumClass} should be a BackedEnum");
                $this->assertTrue($case instanceof \UnitEnum, "Enum case in {$enumClass} should be a UnitEnum");
            }
        }
    }

    public function test_all_enums_have_cases(): void
    {
        $enums = [
            AccessRestrictionType::class,
            AssetAccessRole::class,
            AssetAccountType::class,
            AuditAction::class,
            CacheKey::class,
            Dbms::class,
            DatabaseScope::class,
            RequestStatus::class,
            RiskRating::class,
            SessionFlag::class,
            SessionStatus::class,
            Status::class,
            UserRole::class,
        ];

        foreach ($enums as $enumClass) {
            $cases = $enumClass::cases();
            $this->assertIsArray($cases, "Enum {$enumClass} should return array of cases");
            $this->assertGreaterThan(0, count($cases), "Enum {$enumClass} should have at least one case");
        }
    }

    public function test_all_enums_have_valid_values(): void
    {
        $enums = [
            AccessRestrictionType::class,
            AssetAccessRole::class,
            AssetAccountType::class,
            AuditAction::class,
            CacheKey::class,
            Dbms::class,
            DatabaseScope::class,
            RequestStatus::class,
            RiskRating::class,
            SessionFlag::class,
            SessionStatus::class,
            Status::class,
            UserRole::class,
        ];

        foreach ($enums as $enumClass) {
            $cases = $enumClass::cases();

            foreach ($cases as $case) {
                $this->assertIsString($case->value, "Enum case value in {$enumClass} should be string");
                $this->assertNotEmpty($case->value, "Enum case value in {$enumClass} should not be empty");
                $this->assertIsString($case->name, "Enum case name in {$enumClass} should be string");
                $this->assertNotEmpty($case->name, "Enum case name in {$enumClass} should not be empty");
            }
        }
    }

    public function test_all_enums_support_from_method(): void
    {
        $enums = [
            AccessRestrictionType::class,
            AssetAccessRole::class,
            AssetAccountType::class,
            AuditAction::class,
            CacheKey::class,
            Dbms::class,
            DatabaseScope::class,
            RequestStatus::class,
            RiskRating::class,
            SessionFlag::class,
            SessionStatus::class,
            Status::class,
            UserRole::class,
        ];

        foreach ($enums as $enumClass) {
            $cases = $enumClass::cases();

            foreach ($cases as $case) {
                $fromResult = $enumClass::from($case->value);
                $this->assertEquals($case, $fromResult, "from() method should return correct case for {$enumClass}");
            }
        }
    }

    public function test_all_enums_support_try_from_method(): void
    {
        $enums = [
            AccessRestrictionType::class,
            AssetAccessRole::class,
            AssetAccountType::class,
            AuditAction::class,
            CacheKey::class,
            Dbms::class,
            DatabaseScope::class,
            RequestStatus::class,
            RiskRating::class,
            SessionFlag::class,
            SessionStatus::class,
            Status::class,
            UserRole::class,
        ];

        foreach ($enums as $enumClass) {
            $cases = $enumClass::cases();

            foreach ($cases as $case) {
                $tryFromResult = $enumClass::tryFrom($case->value);
                $this->assertEquals($case, $tryFromResult, "tryFrom() method should return correct case for {$enumClass}");
            }

            // Test invalid value returns null
            $invalidResult = $enumClass::tryFrom('invalid_value_that_does_not_exist');
            $this->assertNull($invalidResult, "tryFrom() should return null for invalid value in {$enumClass}");
        }
    }

    public function test_all_enums_throw_exception_for_invalid_from(): void
    {
        $enums = [
            AccessRestrictionType::class,
            AssetAccessRole::class,
            AssetAccountType::class,
            AuditAction::class,
            CacheKey::class,
            Dbms::class,
            DatabaseScope::class,
            RequestStatus::class,
            RiskRating::class,
            SessionFlag::class,
            SessionStatus::class,
            Status::class,
            UserRole::class,
        ];

        foreach ($enums as $enumClass) {
            $this->expectException(\ValueError::class);
            $enumClass::from('invalid_value_that_does_not_exist');
        }
    }

    public function test_all_enums_are_serializable(): void
    {
        $enums = [
            AccessRestrictionType::class,
            AssetAccessRole::class,
            AssetAccountType::class,
            AuditAction::class,
            CacheKey::class,
            Dbms::class,
            DatabaseScope::class,
            RequestStatus::class,
            RiskRating::class,
            SessionFlag::class,
            SessionStatus::class,
            Status::class,
            UserRole::class,
        ];

        foreach ($enums as $enumClass) {
            $cases = $enumClass::cases();

            foreach ($cases as $case) {
                $serialized = serialize($case);
                $this->assertIsString($serialized, "Enum case in {$enumClass} should be serializable");

                $unserialized = unserialize($serialized);
                $this->assertEquals($case, $unserialized, "Unserialized enum case should equal original in {$enumClass}");
            }
        }
    }

    public function test_all_enums_are_json_serializable(): void
    {
        $enums = [
            AccessRestrictionType::class,
            AssetAccessRole::class,
            AssetAccountType::class,
            AuditAction::class,
            CacheKey::class,
            Dbms::class,
            DatabaseScope::class,
            RequestStatus::class,
            RiskRating::class,
            SessionFlag::class,
            SessionStatus::class,
            Status::class,
            UserRole::class,
        ];

        foreach ($enums as $enumClass) {
            $cases = $enumClass::cases();

            foreach ($cases as $case) {
                $json = json_encode($case);
                $this->assertIsString($json, "Enum case in {$enumClass} should be JSON serializable");
                $this->assertStringStartsWith('"', $json, "JSON encoded enum case should be quoted string in {$enumClass}");
                $this->assertStringEndsWith('"', $json, "JSON encoded enum case should be quoted string in {$enumClass}");
            }
        }
    }

    public function test_all_enums_support_equality_comparison(): void
    {
        $enums = [
            AccessRestrictionType::class,
            AssetAccessRole::class,
            AssetAccountType::class,
            AuditAction::class,
            CacheKey::class,
            Dbms::class,
            DatabaseScope::class,
            RequestStatus::class,
            RiskRating::class,
            SessionFlag::class,
            SessionStatus::class,
            Status::class,
            UserRole::class,
        ];

        foreach ($enums as $enumClass) {
            $cases = $enumClass::cases();

            foreach ($cases as $case) {
                $this->assertTrue($case === $case, "Enum case should equal itself (===) in {$enumClass}");
                $this->assertTrue($case == $case, "Enum case should equal itself (==) in {$enumClass}");

                // Test with different cases
                foreach ($cases as $otherCase) {
                    if ($case !== $otherCase) {
                        $this->assertFalse($case === $otherCase, "Different enum cases should not be equal (===) in {$enumClass}");
                        $this->assertFalse($case == $otherCase, "Different enum cases should not be equal (==) in {$enumClass}");
                    }
                }
            }
        }
    }

    public function test_all_enums_support_switch_statements(): void
    {
        $enums = [
            AccessRestrictionType::class,
            AssetAccessRole::class,
            AssetAccountType::class,
            AuditAction::class,
            CacheKey::class,
            Dbms::class,
            DatabaseScope::class,
            RequestStatus::class,
            RiskRating::class,
            SessionFlag::class,
            SessionStatus::class,
            Status::class,
            UserRole::class,
        ];

        foreach ($enums as $enumClass) {
            $cases = $enumClass::cases();

            foreach ($cases as $case) {
                $result = match ($case) {
                    default => 'matched',
                };

                $this->assertEquals('matched', $result, "Enum case should work in match expression in {$enumClass}");
            }
        }
    }

    public function test_all_enums_have_unique_values(): void
    {
        $enums = [
            AccessRestrictionType::class,
            AssetAccessRole::class,
            AssetAccountType::class,
            AuditAction::class,
            CacheKey::class,
            Dbms::class,
            DatabaseScope::class,
            RequestStatus::class,
            RiskRating::class,
            SessionFlag::class,
            SessionStatus::class,
            Status::class,
            UserRole::class,
        ];

        foreach ($enums as $enumClass) {
            $cases = $enumClass::cases();
            $values = array_column($cases, 'value');
            $uniqueValues = array_unique($values);

            $this->assertEquals(count($values), count($uniqueValues), "Enum values should be unique in {$enumClass}");
        }
    }

    public function test_all_enums_have_unique_names(): void
    {
        $enums = [
            AccessRestrictionType::class,
            AssetAccessRole::class,
            AssetAccountType::class,
            AuditAction::class,
            CacheKey::class,
            Dbms::class,
            DatabaseScope::class,
            RequestStatus::class,
            RiskRating::class,
            SessionFlag::class,
            SessionStatus::class,
            Status::class,
            UserRole::class,
        ];

        foreach ($enums as $enumClass) {
            $cases = $enumClass::cases();
            $names = array_column($cases, 'name');
            $uniqueNames = array_unique($names);

            $this->assertEquals(count($names), count($uniqueNames), "Enum names should be unique in {$enumClass}");
        }
    }

    public function test_all_enums_are_iterable(): void
    {
        $enums = [
            AccessRestrictionType::class,
            AssetAccessRole::class,
            AssetAccountType::class,
            AuditAction::class,
            CacheKey::class,
            Dbms::class,
            DatabaseScope::class,
            RequestStatus::class,
            RiskRating::class,
            SessionFlag::class,
            SessionStatus::class,
            Status::class,
            UserRole::class,
        ];

        foreach ($enums as $enumClass) {
            $cases = $enumClass::cases();
            $iteratedCases = [];

            foreach ($cases as $case) {
                $iteratedCases[] = $case;
            }

            $this->assertEquals($cases, $iteratedCases, "Enum cases should be iterable in {$enumClass}");
        }
    }

    public function test_all_enums_support_array_functions(): void
    {
        $enums = [
            AccessRestrictionType::class,
            AssetAccessRole::class,
            AssetAccountType::class,
            AuditAction::class,
            CacheKey::class,
            Dbms::class,
            DatabaseScope::class,
            RequestStatus::class,
            RiskRating::class,
            SessionFlag::class,
            SessionStatus::class,
            Status::class,
            UserRole::class,
        ];

        foreach ($enums as $enumClass) {
            $cases = $enumClass::cases();

            // Test array_column
            $values = array_column($cases, 'value');
            $names = array_column($cases, 'name');

            $this->assertIsArray($values, "array_column should work with enum cases in {$enumClass}");
            $this->assertIsArray($names, "array_column should work with enum cases in {$enumClass}");
            $this->assertCount(count($cases), $values, "array_column should return correct count in {$enumClass}");
            $this->assertCount(count($cases), $names, "array_column should return correct count in {$enumClass}");

            // Test in_array
            foreach ($cases as $case) {
                $this->assertTrue(in_array($case, $cases), "in_array should work with enum cases in {$enumClass}");
            }
        }
    }
}
