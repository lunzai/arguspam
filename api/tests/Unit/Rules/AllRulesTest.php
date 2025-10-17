<?php

namespace Tests\Unit\Rules;

use App\Rules\AssetAccessCompositeUnique;
use App\Rules\IpOrCidr;
use App\Rules\UserExistedInOrg;
use App\Rules\UserGroupExistedInOrg;
use Tests\TestCase;

class AllRulesTest extends TestCase
{
    public function test_all_rules_implement_validation_rule_interface(): void
    {
        $rules = [
            AssetAccessCompositeUnique::class,
            IpOrCidr::class,
            UserExistedInOrg::class,
            UserGroupExistedInOrg::class,
        ];

        foreach ($rules as $ruleClass) {
            $this->assertTrue(
                in_array('Illuminate\Contracts\Validation\ValidationRule', class_implements($ruleClass)),
                "Rule {$ruleClass} should implement ValidationRule interface"
            );
        }
    }

    public function test_all_rules_can_be_instantiated(): void
    {
        // Test AssetAccessCompositeUnique
        $assetRule = new AssetAccessCompositeUnique('test_table', 'user_id', 'admin', '1');
        $this->assertInstanceOf(AssetAccessCompositeUnique::class, $assetRule);

        // Test IpOrCidr
        $ipRule = new IpOrCidr;
        $this->assertInstanceOf(IpOrCidr::class, $ipRule);

        // Test UserExistedInOrg (requires org ID)
        $userRule = new UserExistedInOrg(1);
        $this->assertInstanceOf(UserExistedInOrg::class, $userRule);

        // Test UserGroupExistedInOrg (requires org ID)
        $userGroupRule = new UserGroupExistedInOrg(1);
        $this->assertInstanceOf(UserGroupExistedInOrg::class, $userGroupRule);
    }

    public function test_all_rules_have_validate_method(): void
    {
        $rules = [
            AssetAccessCompositeUnique::class,
            IpOrCidr::class,
            UserExistedInOrg::class,
            UserGroupExistedInOrg::class,
        ];

        foreach ($rules as $ruleClass) {
            $this->assertTrue(
                method_exists($ruleClass, 'validate'),
                "Rule {$ruleClass} should have validate method"
            );
        }
    }

    public function test_all_rules_validate_method_signature(): void
    {
        $rules = [
            AssetAccessCompositeUnique::class,
            IpOrCidr::class,
            UserExistedInOrg::class,
            UserGroupExistedInOrg::class,
        ];

        foreach ($rules as $ruleClass) {
            $reflection = new \ReflectionClass($ruleClass);
            $validateMethod = $reflection->getMethod('validate');

            $this->assertTrue($validateMethod->isPublic(), "Rule {$ruleClass} validate method should be public");

            $parameters = $validateMethod->getParameters();
            $this->assertCount(3, $parameters, "Rule {$ruleClass} validate method should have 3 parameters");

            $this->assertEquals('attribute', $parameters[0]->getName());
            $this->assertEquals('value', $parameters[1]->getName());
            $this->assertEquals('fail', $parameters[2]->getName());
        }
    }

    public function test_all_rules_constructor_parameters(): void
    {
        // Test AssetAccessCompositeUnique constructor
        $reflection = new \ReflectionClass(AssetAccessCompositeUnique::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $this->assertCount(5, $parameters);
        $this->assertEquals('table', $parameters[0]->getName());
        $this->assertEquals('column', $parameters[1]->getName());
        $this->assertEquals('role', $parameters[2]->getName());
        $this->assertEquals('assetId', $parameters[3]->getName());
        $this->assertEquals('ignoreId', $parameters[4]->getName());
        $this->assertTrue($parameters[4]->isOptional());

        // Test IpOrCidr constructor (no parameters)
        $reflection = new \ReflectionClass(IpOrCidr::class);
        $constructor = $reflection->getConstructor();
        $this->assertNull($constructor);

        // Test UserExistedInOrg constructor
        $reflection = new \ReflectionClass(UserExistedInOrg::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('orgId', $parameters[0]->getName());

        // Test UserGroupExistedInOrg constructor
        $reflection = new \ReflectionClass(UserGroupExistedInOrg::class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor->getParameters();

        $this->assertCount(1, $parameters);
        $this->assertEquals('orgId', $parameters[0]->getName());
    }

    public function test_all_rules_are_in_correct_namespace(): void
    {
        $rules = [
            AssetAccessCompositeUnique::class,
            IpOrCidr::class,
            UserExistedInOrg::class,
            UserGroupExistedInOrg::class,
        ];

        foreach ($rules as $ruleClass) {
            $this->assertStringStartsWith('App\Rules', $ruleClass);
        }
    }

    public function test_all_rules_have_proper_documentation(): void
    {
        $rules = [
            AssetAccessCompositeUnique::class,
            IpOrCidr::class,
            UserExistedInOrg::class,
            UserGroupExistedInOrg::class,
        ];

        foreach ($rules as $ruleClass) {
            $reflection = new \ReflectionClass($ruleClass);

            // Check if class has docblock
            $this->assertNotEmpty($reflection->getDocComment(), "Rule {$ruleClass} should have class documentation");

            // Check if validate method has docblock
            $validateMethod = $reflection->getMethod('validate');
            $this->assertNotEmpty($validateMethod->getDocComment(), "Rule {$ruleClass} validate method should have documentation");
        }
    }

    public function test_all_rules_handle_edge_cases(): void
    {
        // Test that IpOrCidr can handle null values without crashing
        $ipRule = new IpOrCidr;
        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        $ipRule->validate('test_attribute', null, $fail);
        $this->assertTrue($failCalled, 'IpOrCidr should fail validation for null values');
    }

    public function test_all_rules_handle_empty_strings(): void
    {
        // Test that IpOrCidr can handle empty strings without crashing
        $ipRule = new IpOrCidr;
        $failCalled = false;
        $fail = function () use (&$failCalled) {
            $failCalled = true;
        };

        $ipRule->validate('test_attribute', '', $fail);
        $this->assertTrue($failCalled, 'IpOrCidr should fail validation for empty strings');
    }

    public function test_all_rules_handle_invalid_types(): void
    {
        // Test that IpOrCidr can handle invalid types without crashing
        $ipRule = new IpOrCidr;
        $invalidValues = [
            [],
            (object) [],
            true,
            false,
            0,
            -1,
            PHP_INT_MAX,
            PHP_INT_MIN,
        ];

        foreach ($invalidValues as $invalidValue) {
            $failCalled = false;
            $fail = function () use (&$failCalled) {
                $failCalled = true;
            };

            $ipRule->validate('test_attribute', $invalidValue, $fail);
            $this->assertTrue($failCalled, 'IpOrCidr should fail validation for invalid types');
        }
    }
}
