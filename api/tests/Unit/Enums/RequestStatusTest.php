<?php

namespace Tests\Unit\Enums;

use App\Enums\RequestStatus;
use PHPUnit\Framework\TestCase;

class RequestStatusTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $cases = RequestStatus::cases();
        $this->assertCount(4, $cases);

        $this->assertContains(RequestStatus::PENDING, $cases);
        $this->assertContains(RequestStatus::APPROVED, $cases);
        $this->assertContains(RequestStatus::REJECTED, $cases);
        $this->assertContains(RequestStatus::EXPIRED, $cases);
    }

    public function test_enum_values()
    {
        $this->assertEquals('pending', RequestStatus::PENDING->value);
        $this->assertEquals('approved', RequestStatus::APPROVED->value);
        $this->assertEquals('rejected', RequestStatus::REJECTED->value);
        $this->assertEquals('expired', RequestStatus::EXPIRED->value);
    }

    public function test_enum_from_string()
    {
        $this->assertEquals(RequestStatus::PENDING, RequestStatus::from('pending'));
        $this->assertEquals(RequestStatus::APPROVED, RequestStatus::from('approved'));
        $this->assertEquals(RequestStatus::REJECTED, RequestStatus::from('rejected'));
        $this->assertEquals(RequestStatus::EXPIRED, RequestStatus::from('expired'));
    }

    public function test_enum_try_from_string()
    {
        $this->assertEquals(RequestStatus::PENDING, RequestStatus::tryFrom('pending'));
        $this->assertEquals(RequestStatus::APPROVED, RequestStatus::tryFrom('approved'));
        $this->assertEquals(RequestStatus::REJECTED, RequestStatus::tryFrom('rejected'));
        $this->assertNull(RequestStatus::tryFrom('invalid'));
    }

    public function test_enum_from_invalid_string_throws_exception()
    {
        $this->expectException(\ValueError::class);
        RequestStatus::from('invalid');
    }
}
