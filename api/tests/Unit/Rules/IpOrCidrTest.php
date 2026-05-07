<?php

namespace Tests\Unit\Rules;

use App\Rules\IpOrCidr;
use Tests\TestCase;

class IpOrCidrTest extends TestCase
{
    private IpOrCidr $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new IpOrCidr;
    }

    public function test_valid_ipv4_passes(): void
    {
        $failed = false;
        $this->rule->validate('ip', '192.168.1.1', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_valid_ipv6_passes(): void
    {
        $failed = false;
        $this->rule->validate('ip', '::1', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_valid_cidr_passes(): void
    {
        $failed = false;
        $this->rule->validate('ip', '192.168.1.0/24', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_invalid_ip_fails(): void
    {
        $message = null;
        $this->rule->validate('ip', 'not-an-ip', function ($msg) use (&$message) {
            $message = $msg;
        });

        $this->assertNotNull($message);
        $this->assertStringContainsString('IP address', $message);
    }

    public function test_null_value_fails(): void
    {
        $message = null;
        $this->rule->validate('ip', null, function ($msg) use (&$message) {
            $message = $msg;
        });

        $this->assertNotNull($message);
    }

    public function test_non_string_fails(): void
    {
        $message = null;
        $this->rule->validate('ip', 12345, function ($msg) use (&$message) {
            $message = $msg;
        });

        $this->assertNotNull($message);
    }

    public function test_cidr_with_invalid_ip_part_fails(): void
    {
        $message = null;
        $this->rule->validate('ip', '999.999.999.999/24', function ($msg) use (&$message) {
            $message = $msg;
        });

        $this->assertNotNull($message);
    }
}
