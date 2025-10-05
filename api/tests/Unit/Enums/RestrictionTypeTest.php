<?php

namespace Tests\Unit\Enums;

use App\Enums\AccessRestrictionType;
use PHPUnit\Framework\TestCase;

class RestrictionTypeTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $cases = AccessRestrictionType::cases();
        $this->assertCount(4, $cases);

        $this->assertContains(AccessRestrictionType::IP_ADDRESS, $cases);
        $this->assertContains(AccessRestrictionType::TIME_WINDOW, $cases);
        $this->assertContains(AccessRestrictionType::COUNTRY, $cases);
    }

    public function test_enum_values()
    {
        $this->assertEquals('ip_address', AccessRestrictionType::IP_ADDRESS->value);
        $this->assertEquals('time_window', AccessRestrictionType::TIME_WINDOW->value);
        $this->assertEquals('country', AccessRestrictionType::COUNTRY->value);
    }

    public function test_enum_from_string()
    {
        $this->assertEquals(AccessRestrictionType::IP_ADDRESS, AccessRestrictionType::from('ip_address'));
        $this->assertEquals(AccessRestrictionType::TIME_WINDOW, AccessRestrictionType::from('time_window'));
        $this->assertEquals(AccessRestrictionType::COUNTRY, AccessRestrictionType::from('country'));
    }

    public function test_enum_try_from_string()
    {
        $this->assertEquals(AccessRestrictionType::IP_ADDRESS, AccessRestrictionType::tryFrom('ip_address'));
        $this->assertEquals(AccessRestrictionType::TIME_WINDOW, AccessRestrictionType::tryFrom('time_window'));
        $this->assertEquals(AccessRestrictionType::COUNTRY, AccessRestrictionType::tryFrom('country'));
        $this->assertNull(AccessRestrictionType::tryFrom('invalid'));
    }

    public function test_enum_from_invalid_string_throws_exception()
    {
        $this->expectException(\ValueError::class);
        AccessRestrictionType::from('invalid');
    }
}
