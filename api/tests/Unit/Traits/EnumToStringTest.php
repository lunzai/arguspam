<?php

namespace Tests\Unit\Traits;

use App\Enums\RiskRating;
use App\Enums\SessionFlag;
use Tests\TestCase;

class EnumToStringTest extends TestCase
{
    public function test_to_string_returns_pipe_delimited_values_by_default(): void
    {
        $result = SessionFlag::toString();

        $this->assertStringContainsString('SECURITY VIOLATION', $result);
        $this->assertStringContainsString('|', $result);
    }

    public function test_to_string_with_custom_delimiter(): void
    {
        $result = SessionFlag::toString(',');

        $this->assertStringContainsString('SECURITY VIOLATION', $result);
        $this->assertStringContainsString(',', $result);
        $this->assertStringNotContainsString('|', $result);
    }

    public function test_to_string_includes_all_enum_values(): void
    {
        $result = SessionFlag::toString('|');
        $values = explode('|', $result);

        $this->assertCount(count(SessionFlag::cases()), $values);
    }

    public function test_risk_rating_to_string(): void
    {
        $result = RiskRating::toString();

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }
}
