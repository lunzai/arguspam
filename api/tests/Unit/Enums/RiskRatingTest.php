<?php

namespace Tests\Unit\Enums;

use App\Enums\RiskRating;
use PHPUnit\Framework\TestCase;

class RiskRatingTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $cases = RiskRating::cases();
        $this->assertCount(4, $cases);

        $this->assertContains(RiskRating::LOW, $cases);
        $this->assertContains(RiskRating::MEDIUM, $cases);
        $this->assertContains(RiskRating::HIGH, $cases);
        $this->assertContains(RiskRating::CRITICAL, $cases);
    }

    public function test_enum_values()
    {
        $this->assertEquals('low', RiskRating::LOW->value);
        $this->assertEquals('medium', RiskRating::MEDIUM->value);
        $this->assertEquals('high', RiskRating::HIGH->value);
        $this->assertEquals('critical', RiskRating::CRITICAL->value);
    }

    public function test_enum_from_string()
    {
        $this->assertEquals(RiskRating::LOW, RiskRating::from('low'));
        $this->assertEquals(RiskRating::MEDIUM, RiskRating::from('medium'));
        $this->assertEquals(RiskRating::HIGH, RiskRating::from('high'));
        $this->assertEquals(RiskRating::CRITICAL, RiskRating::from('critical'));
    }

    public function test_enum_try_from_string()
    {
        $this->assertEquals(RiskRating::LOW, RiskRating::tryFrom('low'));
        $this->assertEquals(RiskRating::MEDIUM, RiskRating::tryFrom('medium'));
        $this->assertEquals(RiskRating::HIGH, RiskRating::tryFrom('high'));
        $this->assertNull(RiskRating::tryFrom('invalid'));
    }

    public function test_enum_from_invalid_string_throws_exception()
    {
        $this->expectException(\ValueError::class);
        RiskRating::from('invalid');
    }
}
