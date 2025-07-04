<?php

namespace Tests\Unit\Enums;

use App\Enums\SessionStatus;
use PHPUnit\Framework\TestCase;

class SessionStatusTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $cases = SessionStatus::cases();
        $this->assertCount(5, $cases);
        
        $this->assertContains(SessionStatus::SCHEDULED, $cases);
        $this->assertContains(SessionStatus::ACTIVE, $cases);
        $this->assertContains(SessionStatus::EXPIRED, $cases);
        $this->assertContains(SessionStatus::TERMINATED, $cases);
        $this->assertContains(SessionStatus::ENDED, $cases);
    }

    public function test_enum_values()
    {
        $this->assertEquals('scheduled', SessionStatus::SCHEDULED->value);
        $this->assertEquals('active', SessionStatus::ACTIVE->value);
        $this->assertEquals('expired', SessionStatus::EXPIRED->value);
        $this->assertEquals('terminated', SessionStatus::TERMINATED->value);
        $this->assertEquals('ended', SessionStatus::ENDED->value);
    }

    public function test_enum_from_string()
    {
        $this->assertEquals(SessionStatus::SCHEDULED, SessionStatus::from('scheduled'));
        $this->assertEquals(SessionStatus::ACTIVE, SessionStatus::from('active'));
        $this->assertEquals(SessionStatus::EXPIRED, SessionStatus::from('expired'));
        $this->assertEquals(SessionStatus::TERMINATED, SessionStatus::from('terminated'));
        $this->assertEquals(SessionStatus::ENDED, SessionStatus::from('ended'));
    }

    public function test_enum_try_from_string()
    {
        $this->assertEquals(SessionStatus::SCHEDULED, SessionStatus::tryFrom('scheduled'));
        $this->assertEquals(SessionStatus::ACTIVE, SessionStatus::tryFrom('active'));
        $this->assertEquals(SessionStatus::EXPIRED, SessionStatus::tryFrom('expired'));
        $this->assertNull(SessionStatus::tryFrom('invalid'));
    }

    public function test_enum_from_invalid_string_throws_exception()
    {
        $this->expectException(\ValueError::class);
        SessionStatus::from('invalid');
    }
}