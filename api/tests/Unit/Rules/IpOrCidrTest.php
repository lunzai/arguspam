<?php

namespace Tests\Unit\Rules;

use App\Rules\IpOrCidr;
use PHPUnit\Framework\TestCase;

class IpOrCidrTest extends TestCase
{
    private IpOrCidr $rule;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rule = new IpOrCidr();
    }

    public function test_validates_valid_ipv4_addresses(): void
    {
        $validIps = [
            '192.168.1.1',
            '10.0.0.1',
            '172.16.0.1',
            '127.0.0.1',
            '8.8.8.8',
            '255.255.255.255',
            '0.0.0.0'
        ];

        foreach ($validIps as $ip) {
            $failCalled = false;
            $fail = function() use (&$failCalled) {
                $failCalled = true;
            };

            $this->rule->validate('ip_address', $ip, $fail);

            $this->assertFalse($failCalled, "IP address {$ip} should be valid");
        }
    }

    public function test_validates_valid_ipv6_addresses(): void
    {
        $validIps = [
            '::1',
            '2001:db8::1',
            'fe80::1',
            '::',
            '2001:0db8:85a3:0000:0000:8a2e:0370:7334'
        ];

        foreach ($validIps as $ip) {
            $failCalled = false;
            $fail = function() use (&$failCalled) {
                $failCalled = true;
            };

            $this->rule->validate('ip_address', $ip, $fail);

            $this->assertFalse($failCalled, "IPv6 address {$ip} should be valid");
        }
    }

    public function test_validates_valid_cidr_notation(): void
    {
        $validCidrs = [
            '192.168.1.0/24',
            '10.0.0.0/8',
            '172.16.0.0/16',
            '127.0.0.0/8',
            '192.168.1.1/32',
            '0.0.0.0/0',
            '255.255.255.255/32'
        ];

        foreach ($validCidrs as $cidr) {
            $failCalled = false;
            $fail = function() use (&$failCalled) {
                $failCalled = true;
            };

            $this->rule->validate('ip_address', $cidr, $fail);

            $this->assertFalse($failCalled, "CIDR notation {$cidr} should be valid");
        }
    }

    public function test_rejects_invalid_ip_addresses(): void
    {
        $invalidIps = [
            '256.256.256.256',
            '192.168.1',
            '192.168.1.1.1',
            '192.168.1.a',
            'invalid-ip',
            '192.168.1.-1',
            '192.168.1.999'
        ];

        foreach ($invalidIps as $ip) {
            $failCalled = false;
            $fail = function() use (&$failCalled) {
                $failCalled = true;
            };

            $this->rule->validate('ip_address', $ip, $fail);

            $this->assertTrue($failCalled, "IP address {$ip} should be invalid");
        }
    }

    public function test_rejects_invalid_cidr_notation(): void
    {
        $invalidCidrs = [
            '192.168.1.0/33',
            '192.168.1.0/-1',
            '192.168.1.0/abc',
            '192.168.1.0/',
            '192.168.1.0/24/extra',
            '256.256.256.256/24',
            '192.168.1.0/100',
            '192.168.1.0/24/24'
        ];

        foreach ($invalidCidrs as $cidr) {
            $failCalled = false;
            $fail = function() use (&$failCalled) {
                $failCalled = true;
            };

            $this->rule->validate('ip_address', $cidr, $fail);

            $this->assertTrue($failCalled, "CIDR notation {$cidr} should be invalid");
        }
    }

    public function test_rejects_empty_string(): void
    {
        $failCalled = false;
        $fail = function() use (&$failCalled) {
            $failCalled = true;
        };

        $this->rule->validate('ip_address', '', $fail);

        $this->assertTrue($failCalled, "Empty string should be invalid");
    }

    public function test_rejects_null_value(): void
    {
        $failCalled = false;
        $fail = function() use (&$failCalled) {
            $failCalled = true;
        };

        $this->rule->validate('ip_address', null, $fail);

        $this->assertTrue($failCalled, "Null value should be invalid");
    }

    public function test_fail_callback_receives_correct_message(): void
    {
        $failMessage = '';
        $fail = function($message) use (&$failMessage) {
            $failMessage = $message;
        };

        $this->rule->validate('test_field', 'invalid-ip', $fail);

        $this->assertEquals('The :attribute must be a valid IP address or CIDR notation.', $failMessage);
    }

    public function test_validates_edge_case_cidr_values(): void
    {
        $edgeCaseCidrs = [
            '192.168.1.0/0',  // Minimum subnet
            '192.168.1.0/32', // Maximum subnet
            '0.0.0.0/0',      // Any network
            '255.255.255.255/32' // Single host
        ];

        foreach ($edgeCaseCidrs as $cidr) {
            $failCalled = false;
            $fail = function() use (&$failCalled) {
                $failCalled = true;
            };

            $this->rule->validate('ip_address', $cidr, $fail);

            $this->assertFalse($failCalled, "Edge case CIDR {$cidr} should be valid");
        }
    }

    public function test_rejects_cidr_with_invalid_ip_part(): void
    {
        $invalidCidrs = [
            '256.256.256.256/24',
            '192.168.1.999/24',
            '192.168.1.a/24',
            '192.168.1/24'
        ];

        foreach ($invalidCidrs as $cidr) {
            $failCalled = false;
            $fail = function() use (&$failCalled) {
                $failCalled = true;
            };

            $this->rule->validate('ip_address', $cidr, $fail);

            $this->assertTrue($failCalled, "CIDR with invalid IP part {$cidr} should be invalid");
        }
    }
}