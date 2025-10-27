<?php

namespace Tests\Unit\Enums;

use App\Enums\SessionFlag;
use PHPUnit\Framework\TestCase;

class SessionFlagTest extends TestCase
{
    public function test_enum_cases_exist(): void
    {
        $cases = SessionFlag::cases();
        $this->assertCount(5, $cases);

        $this->assertContains(SessionFlag::SECURITY_VIOLATION, $cases);
        $this->assertContains(SessionFlag::COMPLIANCE_VIOLATION, $cases);
        $this->assertContains(SessionFlag::DATA_MISUSE, $cases);
        $this->assertContains(SessionFlag::ANOMALOUS_BEHAVIOR, $cases);
        $this->assertContains(SessionFlag::SYSTEM_INTEGRITY_RISK, $cases);
    }

    public function test_enum_values(): void
    {
        $this->assertEquals('SECURITY VIOLATION', SessionFlag::SECURITY_VIOLATION->value);
        $this->assertEquals('COMPLIANCE VIOLATION', SessionFlag::COMPLIANCE_VIOLATION->value);
        $this->assertEquals('DATA MISUSE', SessionFlag::DATA_MISUSE->value);
        $this->assertEquals('ANOMALOUS BEHAVIOR', SessionFlag::ANOMALOUS_BEHAVIOR->value);
        $this->assertEquals('SYSTEM INTEGRITY RISK', SessionFlag::SYSTEM_INTEGRITY_RISK->value);
    }

    public function test_enum_from_string(): void
    {
        $this->assertEquals(SessionFlag::SECURITY_VIOLATION, SessionFlag::from('SECURITY VIOLATION'));
        $this->assertEquals(SessionFlag::COMPLIANCE_VIOLATION, SessionFlag::from('COMPLIANCE VIOLATION'));
        $this->assertEquals(SessionFlag::DATA_MISUSE, SessionFlag::from('DATA MISUSE'));
        $this->assertEquals(SessionFlag::ANOMALOUS_BEHAVIOR, SessionFlag::from('ANOMALOUS BEHAVIOR'));
        $this->assertEquals(SessionFlag::SYSTEM_INTEGRITY_RISK, SessionFlag::from('SYSTEM INTEGRITY RISK'));
    }

    public function test_enum_try_from_string(): void
    {
        $this->assertEquals(SessionFlag::SECURITY_VIOLATION, SessionFlag::tryFrom('SECURITY VIOLATION'));
        $this->assertEquals(SessionFlag::COMPLIANCE_VIOLATION, SessionFlag::tryFrom('COMPLIANCE VIOLATION'));
        $this->assertEquals(SessionFlag::DATA_MISUSE, SessionFlag::tryFrom('DATA MISUSE'));
        $this->assertEquals(SessionFlag::ANOMALOUS_BEHAVIOR, SessionFlag::tryFrom('ANOMALOUS BEHAVIOR'));
        $this->assertEquals(SessionFlag::SYSTEM_INTEGRITY_RISK, SessionFlag::tryFrom('SYSTEM INTEGRITY RISK'));
        $this->assertNull(SessionFlag::tryFrom('invalid'));
        $this->assertNull(SessionFlag::tryFrom(''));
        $this->assertNull(SessionFlag::tryFrom(null));
    }

    public function test_enum_from_invalid_string_throws_exception(): void
    {
        $this->expectException(\ValueError::class);
        SessionFlag::from('invalid');
    }

    public function test_enum_from_empty_string_throws_exception(): void
    {
        $this->expectException(\ValueError::class);
        SessionFlag::from('');
    }

    public function test_enum_from_null_throws_exception(): void
    {
        $this->expectException(\ValueError::class);
        SessionFlag::from(null);
    }

    public function test_enum_is_backed_enum(): void
    {
        $this->assertTrue(SessionFlag::SECURITY_VIOLATION instanceof \BackedEnum);
        $this->assertTrue(SessionFlag::COMPLIANCE_VIOLATION instanceof \BackedEnum);
        $this->assertTrue(SessionFlag::DATA_MISUSE instanceof \BackedEnum);
        $this->assertTrue(SessionFlag::ANOMALOUS_BEHAVIOR instanceof \BackedEnum);
        $this->assertTrue(SessionFlag::SYSTEM_INTEGRITY_RISK instanceof \BackedEnum);
    }

    public function test_enum_implements_unit_enum(): void
    {
        $this->assertTrue(SessionFlag::SECURITY_VIOLATION instanceof \UnitEnum);
        $this->assertTrue(SessionFlag::COMPLIANCE_VIOLATION instanceof \UnitEnum);
        $this->assertTrue(SessionFlag::DATA_MISUSE instanceof \UnitEnum);
        $this->assertTrue(SessionFlag::ANOMALOUS_BEHAVIOR instanceof \UnitEnum);
        $this->assertTrue(SessionFlag::SYSTEM_INTEGRITY_RISK instanceof \UnitEnum);
    }

    public function test_enum_name_property(): void
    {
        $this->assertEquals('SECURITY_VIOLATION', SessionFlag::SECURITY_VIOLATION->name);
        $this->assertEquals('COMPLIANCE_VIOLATION', SessionFlag::COMPLIANCE_VIOLATION->name);
        $this->assertEquals('DATA_MISUSE', SessionFlag::DATA_MISUSE->name);
        $this->assertEquals('ANOMALOUS_BEHAVIOR', SessionFlag::ANOMALOUS_BEHAVIOR->name);
        $this->assertEquals('SYSTEM_INTEGRITY_RISK', SessionFlag::SYSTEM_INTEGRITY_RISK->name);
    }

    public function test_enum_to_string(): void
    {
        $this->assertEquals('SECURITY VIOLATION', SessionFlag::SECURITY_VIOLATION->value);
        $this->assertEquals('COMPLIANCE VIOLATION', SessionFlag::COMPLIANCE_VIOLATION->value);
        $this->assertEquals('DATA MISUSE', SessionFlag::DATA_MISUSE->value);
        $this->assertEquals('ANOMALOUS BEHAVIOR', SessionFlag::ANOMALOUS_BEHAVIOR->value);
        $this->assertEquals('SYSTEM INTEGRITY RISK', SessionFlag::SYSTEM_INTEGRITY_RISK->value);
    }

    public function test_enum_serialization(): void
    {
        $security = SessionFlag::SECURITY_VIOLATION;
        $compliance = SessionFlag::COMPLIANCE_VIOLATION;

        // Test serialization
        $serializedSecurity = serialize($security);
        $serializedCompliance = serialize($compliance);

        $this->assertIsString($serializedSecurity);
        $this->assertIsString($serializedCompliance);

        // Test deserialization
        $unserializedSecurity = unserialize($serializedSecurity);
        $unserializedCompliance = unserialize($serializedCompliance);

        $this->assertEquals($security, $unserializedSecurity);
        $this->assertEquals($compliance, $unserializedCompliance);
    }

    public function test_enum_json_serialization(): void
    {
        $security = SessionFlag::SECURITY_VIOLATION;
        $compliance = SessionFlag::COMPLIANCE_VIOLATION;

        $this->assertEquals('"SECURITY VIOLATION"', json_encode($security));
        $this->assertEquals('"COMPLIANCE VIOLATION"', json_encode($compliance));
    }

    public function test_enum_equality(): void
    {
        $security1 = SessionFlag::SECURITY_VIOLATION;
        $security2 = SessionFlag::SECURITY_VIOLATION;
        $compliance = SessionFlag::COMPLIANCE_VIOLATION;

        $this->assertTrue($security1 === $security2);
        $this->assertTrue($security1 == $security2);
        $this->assertFalse($security1 === $compliance);
        $this->assertFalse($security1 == $compliance);
    }

    public function test_enum_switch_statement(): void
    {
        $security = SessionFlag::SECURITY_VIOLATION;
        $compliance = SessionFlag::COMPLIANCE_VIOLATION;
        $dataMisuse = SessionFlag::DATA_MISUSE;
        $anomalous = SessionFlag::ANOMALOUS_BEHAVIOR;
        $systemIntegrity = SessionFlag::SYSTEM_INTEGRITY_RISK;

        $securityResult = match ($security) {
            SessionFlag::SECURITY_VIOLATION => 'security_flag',
            SessionFlag::COMPLIANCE_VIOLATION => 'compliance_flag',
            SessionFlag::DATA_MISUSE => 'data_misuse_flag',
            SessionFlag::ANOMALOUS_BEHAVIOR => 'anomalous_flag',
            SessionFlag::SYSTEM_INTEGRITY_RISK => 'system_integrity_flag',
        };

        $complianceResult = match ($compliance) {
            SessionFlag::SECURITY_VIOLATION => 'security_flag',
            SessionFlag::COMPLIANCE_VIOLATION => 'compliance_flag',
            SessionFlag::DATA_MISUSE => 'data_misuse_flag',
            SessionFlag::ANOMALOUS_BEHAVIOR => 'anomalous_flag',
            SessionFlag::SYSTEM_INTEGRITY_RISK => 'system_integrity_flag',
        };

        $this->assertEquals('security_flag', $securityResult);
        $this->assertEquals('compliance_flag', $complianceResult);
    }

    public function test_enum_in_array(): void
    {
        $validFlags = [
            SessionFlag::SECURITY_VIOLATION,
            SessionFlag::COMPLIANCE_VIOLATION,
            SessionFlag::DATA_MISUSE,
            SessionFlag::ANOMALOUS_BEHAVIOR,
            SessionFlag::SYSTEM_INTEGRITY_RISK,
        ];

        $this->assertTrue(in_array(SessionFlag::SECURITY_VIOLATION, $validFlags));
        $this->assertTrue(in_array(SessionFlag::COMPLIANCE_VIOLATION, $validFlags));
        $this->assertTrue(in_array(SessionFlag::DATA_MISUSE, $validFlags));
        $this->assertTrue(in_array(SessionFlag::ANOMALOUS_BEHAVIOR, $validFlags));
        $this->assertTrue(in_array(SessionFlag::SYSTEM_INTEGRITY_RISK, $validFlags));
    }

    public function test_enum_array_values(): void
    {
        $values = array_column(SessionFlag::cases(), 'value');

        $this->assertContains('SECURITY VIOLATION', $values);
        $this->assertContains('COMPLIANCE VIOLATION', $values);
        $this->assertContains('DATA MISUSE', $values);
        $this->assertContains('ANOMALOUS BEHAVIOR', $values);
        $this->assertContains('SYSTEM INTEGRITY RISK', $values);
        $this->assertCount(5, $values);
    }

    public function test_enum_array_keys(): void
    {
        $keys = array_column(SessionFlag::cases(), 'name');

        $this->assertContains('SECURITY_VIOLATION', $keys);
        $this->assertContains('COMPLIANCE_VIOLATION', $keys);
        $this->assertContains('DATA_MISUSE', $keys);
        $this->assertContains('ANOMALOUS_BEHAVIOR', $keys);
        $this->assertContains('SYSTEM_INTEGRITY_RISK', $keys);
        $this->assertCount(5, $keys);
    }

    public function test_enum_foreach_iteration(): void
    {
        $foundFlags = [];

        foreach (SessionFlag::cases() as $case) {
            $foundFlags[] = $case;
        }

        $this->assertContains(SessionFlag::SECURITY_VIOLATION, $foundFlags);
        $this->assertContains(SessionFlag::COMPLIANCE_VIOLATION, $foundFlags);
        $this->assertContains(SessionFlag::DATA_MISUSE, $foundFlags);
        $this->assertContains(SessionFlag::ANOMALOUS_BEHAVIOR, $foundFlags);
        $this->assertContains(SessionFlag::SYSTEM_INTEGRITY_RISK, $foundFlags);
        $this->assertCount(5, $foundFlags);
    }

    public function test_enum_switch_with_default(): void
    {
        $security = SessionFlag::SECURITY_VIOLATION;
        $compliance = SessionFlag::COMPLIANCE_VIOLATION;

        $securityResult = match ($security) {
            SessionFlag::SECURITY_VIOLATION => 'security_type',
            SessionFlag::COMPLIANCE_VIOLATION => 'compliance_type',
            SessionFlag::DATA_MISUSE => 'data_misuse_type',
            SessionFlag::ANOMALOUS_BEHAVIOR => 'anomalous_type',
            SessionFlag::SYSTEM_INTEGRITY_RISK => 'system_integrity_type',
            default => 'unknown_type',
        };

        $complianceResult = match ($compliance) {
            SessionFlag::SECURITY_VIOLATION => 'security_type',
            SessionFlag::COMPLIANCE_VIOLATION => 'compliance_type',
            SessionFlag::DATA_MISUSE => 'data_misuse_type',
            SessionFlag::ANOMALOUS_BEHAVIOR => 'anomalous_type',
            SessionFlag::SYSTEM_INTEGRITY_RISK => 'system_integrity_type',
            default => 'unknown_type',
        };

        $this->assertEquals('security_type', $securityResult);
        $this->assertEquals('compliance_type', $complianceResult);
    }

    public function test_enum_case_insensitive_from(): void
    {
        // Test that from() is case sensitive
        $this->expectException(\ValueError::class);
        SessionFlag::from('security violation');
    }

    public function test_enum_case_insensitive_try_from(): void
    {
        // Test that tryFrom() is case sensitive
        $this->assertNull(SessionFlag::tryFrom('security violation'));
        $this->assertNull(SessionFlag::tryFrom('compliance violation'));
    }

    public function test_enum_whitespace_handling(): void
    {
        $this->expectException(\ValueError::class);
        SessionFlag::from(' SECURITY VIOLATION ');
    }

    public function test_enum_numeric_strings(): void
    {
        $this->assertNull(SessionFlag::tryFrom('1'));
        $this->assertNull(SessionFlag::tryFrom('0'));
    }

    public function test_enum_boolean_strings(): void
    {
        $this->assertNull(SessionFlag::tryFrom('true'));
        $this->assertNull(SessionFlag::tryFrom('false'));
    }

    public function test_enum_flag_categories(): void
    {
        // Test that we can categorize flags by type
        $securityFlags = [
            SessionFlag::SECURITY_VIOLATION,
        ];

        $complianceFlags = [
            SessionFlag::COMPLIANCE_VIOLATION,
        ];

        $dataFlags = [
            SessionFlag::DATA_MISUSE,
        ];

        $behaviorFlags = [
            SessionFlag::ANOMALOUS_BEHAVIOR,
        ];

        $systemFlags = [
            SessionFlag::SYSTEM_INTEGRITY_RISK,
        ];

        $this->assertContains(SessionFlag::SECURITY_VIOLATION, $securityFlags);
        $this->assertContains(SessionFlag::COMPLIANCE_VIOLATION, $complianceFlags);
        $this->assertContains(SessionFlag::DATA_MISUSE, $dataFlags);
        $this->assertContains(SessionFlag::ANOMALOUS_BEHAVIOR, $behaviorFlags);
        $this->assertContains(SessionFlag::SYSTEM_INTEGRITY_RISK, $systemFlags);
    }

    public function test_enum_flag_severity_levels(): void
    {
        // Test that we can assign severity levels to flags
        $criticalFlags = [
            SessionFlag::SECURITY_VIOLATION,
            SessionFlag::COMPLIANCE_VIOLATION,
        ];

        $highFlags = [
            SessionFlag::DATA_MISUSE,
            SessionFlag::SYSTEM_INTEGRITY_RISK,
        ];

        $mediumFlags = [
            SessionFlag::ANOMALOUS_BEHAVIOR,
        ];

        $this->assertContains(SessionFlag::SECURITY_VIOLATION, $criticalFlags);
        $this->assertContains(SessionFlag::COMPLIANCE_VIOLATION, $criticalFlags);
        $this->assertContains(SessionFlag::DATA_MISUSE, $highFlags);
        $this->assertContains(SessionFlag::SYSTEM_INTEGRITY_RISK, $highFlags);
        $this->assertContains(SessionFlag::ANOMALOUS_BEHAVIOR, $mediumFlags);
    }

    public function test_enum_flag_descriptions(): void
    {
        // Test that flag values contain descriptive information
        $this->assertStringContainsString('SECURITY', SessionFlag::SECURITY_VIOLATION->value);
        $this->assertStringContainsString('COMPLIANCE', SessionFlag::COMPLIANCE_VIOLATION->value);
        $this->assertStringContainsString('DATA', SessionFlag::DATA_MISUSE->value);
        $this->assertStringContainsString('ANOMALOUS', SessionFlag::ANOMALOUS_BEHAVIOR->value);
        $this->assertStringContainsString('SYSTEM', SessionFlag::SYSTEM_INTEGRITY_RISK->value);
    }

    public function test_enum_flag_contains_violation_keyword(): void
    {
        // Test that most flags contain "VIOLATION" keyword
        $violationFlags = [
            SessionFlag::SECURITY_VIOLATION,
            SessionFlag::COMPLIANCE_VIOLATION,
        ];

        foreach ($violationFlags as $flag) {
            $this->assertStringContainsString('VIOLATION', $flag->value);
        }
    }

    public function test_enum_flag_contains_behavior_keyword(): void
    {
        // Test that behavior-related flags contain "BEHAVIOR" keyword
        $behaviorFlags = [
            SessionFlag::ANOMALOUS_BEHAVIOR,
        ];

        foreach ($behaviorFlags as $flag) {
            $this->assertStringContainsString('BEHAVIOR', $flag->value);
        }
    }

    public function test_enum_flag_contains_misuse_keyword(): void
    {
        // Test that data-related flags contain "MISUSE" keyword
        $dataFlags = [
            SessionFlag::DATA_MISUSE,
        ];

        foreach ($dataFlags as $flag) {
            $this->assertStringContainsString('MISUSE', $flag->value);
        }
    }

    public function test_enum_flag_contains_risk_keyword(): void
    {
        // Test that system-related flags contain "RISK" keyword
        $systemFlags = [
            SessionFlag::SYSTEM_INTEGRITY_RISK,
        ];

        foreach ($systemFlags as $flag) {
            $this->assertStringContainsString('RISK', $flag->value);
        }
    }
}
