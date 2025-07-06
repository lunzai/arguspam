<?php

namespace Tests\Unit\Enums;

use App\Enums\AssetAccessRole;
use PHPUnit\Framework\TestCase;

class AssetAccessRoleTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $cases = AssetAccessRole::cases();
        $this->assertCount(3, $cases);
        
        $this->assertContains(AssetAccessRole::REQUESTER, $cases);
        $this->assertContains(AssetAccessRole::APPROVER, $cases);
        $this->assertContains(AssetAccessRole::AUDITOR, $cases);
    }

    public function test_enum_values()
    {
        $this->assertEquals('requester', AssetAccessRole::REQUESTER->value);
        $this->assertEquals('approver', AssetAccessRole::APPROVER->value);
        $this->assertEquals('auditor', AssetAccessRole::AUDITOR->value);
    }

    public function test_enum_from_string()
    {
        $this->assertEquals(AssetAccessRole::REQUESTER, AssetAccessRole::from('requester'));
        $this->assertEquals(AssetAccessRole::APPROVER, AssetAccessRole::from('approver'));
        $this->assertEquals(AssetAccessRole::AUDITOR, AssetAccessRole::from('auditor'));
    }

    public function test_enum_try_from_string()
    {
        $this->assertEquals(AssetAccessRole::REQUESTER, AssetAccessRole::tryFrom('requester'));
        $this->assertEquals(AssetAccessRole::APPROVER, AssetAccessRole::tryFrom('approver'));
        $this->assertEquals(AssetAccessRole::AUDITOR, AssetAccessRole::tryFrom('auditor'));
        $this->assertNull(AssetAccessRole::tryFrom('invalid'));
    }

    public function test_enum_from_invalid_string_throws_exception()
    {
        $this->expectException(\ValueError::class);
        AssetAccessRole::from('invalid');
    }
}