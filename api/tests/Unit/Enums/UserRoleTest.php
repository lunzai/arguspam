<?php

namespace Tests\Unit\Enums;

use App\Enums\UserRole;
use PHPUnit\Framework\TestCase;

class UserRoleTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $cases = UserRole::cases();
        $this->assertCount(3, $cases);

        $this->assertContains(UserRole::ADMIN, $cases);
        $this->assertContains(UserRole::USER, $cases);
        $this->assertContains(UserRole::AUDITOR, $cases);
    }

    public function test_enum_values()
    {
        $this->assertEquals('admin', UserRole::ADMIN->value);
        $this->assertEquals('user', UserRole::USER->value);
        $this->assertEquals('auditor', UserRole::AUDITOR->value);
    }

    public function test_enum_from_string()
    {
        $this->assertEquals(UserRole::ADMIN, UserRole::from('admin'));
        $this->assertEquals(UserRole::USER, UserRole::from('user'));
        $this->assertEquals(UserRole::AUDITOR, UserRole::from('auditor'));
    }

    public function test_enum_try_from_string()
    {
        $this->assertEquals(UserRole::ADMIN, UserRole::tryFrom('admin'));
        $this->assertEquals(UserRole::USER, UserRole::tryFrom('user'));
        $this->assertEquals(UserRole::AUDITOR, UserRole::tryFrom('auditor'));
        $this->assertNull(UserRole::tryFrom('invalid'));
    }

    public function test_enum_from_invalid_string_throws_exception()
    {
        $this->expectException(\ValueError::class);
        UserRole::from('invalid');
    }
}
