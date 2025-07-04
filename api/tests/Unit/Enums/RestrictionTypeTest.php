<?php

namespace Tests\Unit\Enums;

use App\Enums\RestrictionType;
use PHPUnit\Framework\TestCase;

class RestrictionTypeTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $cases = RestrictionType::cases();
        $this->assertCount(4, $cases);
        
        $this->assertContains(RestrictionType::IP_ADDRESS, $cases);
        $this->assertContains(RestrictionType::TIME_WINDOW, $cases);
        $this->assertContains(RestrictionType::LOCATION, $cases);
        $this->assertContains(RestrictionType::DEVICE, $cases);
    }

    public function test_enum_values()
    {
        $this->assertEquals('ip_address', RestrictionType::IP_ADDRESS->value);
        $this->assertEquals('time_window', RestrictionType::TIME_WINDOW->value);
        $this->assertEquals('location', RestrictionType::LOCATION->value);
        $this->assertEquals('device', RestrictionType::DEVICE->value);
    }

    public function test_enum_from_string()
    {
        $this->assertEquals(RestrictionType::IP_ADDRESS, RestrictionType::from('ip_address'));
        $this->assertEquals(RestrictionType::TIME_WINDOW, RestrictionType::from('time_window'));
        $this->assertEquals(RestrictionType::LOCATION, RestrictionType::from('location'));
        $this->assertEquals(RestrictionType::DEVICE, RestrictionType::from('device'));
    }

    public function test_enum_try_from_string()
    {
        $this->assertEquals(RestrictionType::IP_ADDRESS, RestrictionType::tryFrom('ip_address'));
        $this->assertEquals(RestrictionType::TIME_WINDOW, RestrictionType::tryFrom('time_window'));
        $this->assertEquals(RestrictionType::LOCATION, RestrictionType::tryFrom('location'));
        $this->assertNull(RestrictionType::tryFrom('invalid'));
    }

    public function test_enum_from_invalid_string_throws_exception()
    {
        $this->expectException(\ValueError::class);
        RestrictionType::from('invalid');
    }
}