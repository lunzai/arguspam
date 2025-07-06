<?php

namespace Tests\Unit\Enums;

use App\Enums\AuditAction;
use PHPUnit\Framework\TestCase;

class AuditActionTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $cases = AuditAction::cases();
        $this->assertCount(15, $cases);
        
        $this->assertContains(AuditAction::VIEW, $cases);
        $this->assertContains(AuditAction::CREATE, $cases);
        $this->assertContains(AuditAction::UPDATE, $cases);
        $this->assertContains(AuditAction::DELETE, $cases);
        $this->assertContains(AuditAction::LOGIN, $cases);
        $this->assertContains(AuditAction::LOGOUT, $cases);
        $this->assertContains(AuditAction::FORGET_PASSWORD, $cases);
        $this->assertContains(AuditAction::RESET_PASSWORD, $cases);
        $this->assertContains(AuditAction::REQUEST_ACCESS, $cases);
        $this->assertContains(AuditAction::REQUEST_APPROVE, $cases);
        $this->assertContains(AuditAction::REQUEST_REJECT, $cases);
        $this->assertContains(AuditAction::SESSION_START, $cases);
        $this->assertContains(AuditAction::SESSION_END, $cases);
        $this->assertContains(AuditAction::SESSION_EXPIRE, $cases);
        $this->assertContains(AuditAction::SESSION_TERMINATE, $cases);
    }

    public function test_enum_values()
    {
        $this->assertEquals('view', AuditAction::VIEW->value);
        $this->assertEquals('create', AuditAction::CREATE->value);
        $this->assertEquals('update', AuditAction::UPDATE->value);
        $this->assertEquals('delete', AuditAction::DELETE->value);
        $this->assertEquals('login', AuditAction::LOGIN->value);
        $this->assertEquals('logout', AuditAction::LOGOUT->value);
        $this->assertEquals('forget password', AuditAction::FORGET_PASSWORD->value);
        $this->assertEquals('reset password', AuditAction::RESET_PASSWORD->value);
        $this->assertEquals('request access', AuditAction::REQUEST_ACCESS->value);
        $this->assertEquals('request approve', AuditAction::REQUEST_APPROVE->value);
        $this->assertEquals('request reject', AuditAction::REQUEST_REJECT->value);
        $this->assertEquals('session start', AuditAction::SESSION_START->value);
        $this->assertEquals('session end', AuditAction::SESSION_END->value);
        $this->assertEquals('session expire', AuditAction::SESSION_EXPIRE->value);
        $this->assertEquals('session terminate', AuditAction::SESSION_TERMINATE->value);
    }

    public function test_enum_from_string()
    {
        $this->assertEquals(AuditAction::VIEW, AuditAction::from('view'));
        $this->assertEquals(AuditAction::CREATE, AuditAction::from('create'));
        $this->assertEquals(AuditAction::SESSION_START, AuditAction::from('session start'));
        $this->assertEquals(AuditAction::FORGET_PASSWORD, AuditAction::from('forget password'));
    }

    public function test_enum_try_from_string()
    {
        $this->assertEquals(AuditAction::VIEW, AuditAction::tryFrom('view'));
        $this->assertEquals(AuditAction::CREATE, AuditAction::tryFrom('create'));
        $this->assertEquals(AuditAction::SESSION_START, AuditAction::tryFrom('session start'));
        $this->assertNull(AuditAction::tryFrom('invalid'));
    }

    public function test_enum_from_invalid_string_throws_exception()
    {
        $this->expectException(\ValueError::class);
        AuditAction::from('invalid');
    }
}