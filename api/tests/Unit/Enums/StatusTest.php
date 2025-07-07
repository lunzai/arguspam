<?php

namespace Tests\Unit\Enums;

use App\Enums\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $cases = Status::cases();
        $this->assertCount(2, $cases);

        $this->assertContains(Status::ACTIVE, $cases);
        $this->assertContains(Status::INACTIVE, $cases);
    }

    public function test_enum_values()
    {
        $this->assertEquals('active', Status::ACTIVE->value);
        $this->assertEquals('inactive', Status::INACTIVE->value);
    }

    public function test_enum_from_string()
    {
        $this->assertEquals(Status::ACTIVE, Status::from('active'));
        $this->assertEquals(Status::INACTIVE, Status::from('inactive'));
    }

    public function test_enum_try_from_string()
    {
        $this->assertEquals(Status::ACTIVE, Status::tryFrom('active'));
        $this->assertEquals(Status::INACTIVE, Status::tryFrom('inactive'));
        $this->assertNull(Status::tryFrom('invalid'));
    }

    public function test_enum_from_invalid_string_throws_exception()
    {
        $this->expectException(\ValueError::class);
        Status::from('invalid');
    }
}
