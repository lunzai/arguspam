<?php

namespace Tests\Unit\Enums;

use App\Enums\CacheKey;
use PHPUnit\Framework\TestCase;

class CacheKeyTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $cases = CacheKey::cases();
        $this->assertCount(34, $cases);

        // Test original 8 cases still exist
        $this->assertContains(CacheKey::USER_PERMISSIONS, $cases);
        $this->assertContains(CacheKey::USER_ROLES, $cases);
        $this->assertContains(CacheKey::ORG_USERS, $cases);

        // Test some of the newer cases
        $this->assertContains(CacheKey::TIMEZONES, $cases);
        $this->assertContains(CacheKey::USERS, $cases);
        $this->assertContains(CacheKey::ROLES, $cases);
        $this->assertContains(CacheKey::PERMISSIONS, $cases);
        $this->assertContains(CacheKey::ASSETS, $cases);
        $this->assertContains(CacheKey::REQUESTS, $cases);
        $this->assertContains(CacheKey::SESSIONS, $cases);
    }

    public function test_enum_values()
    {
        $this->assertEquals('user:permission', CacheKey::USER_PERMISSIONS->value);
        $this->assertEquals('user:role', CacheKey::USER_ROLES->value);
        $this->assertEquals('org_users', CacheKey::ORG_USERS->value);
    }

    public function test_enum_from_string()
    {
        $this->assertEquals(CacheKey::USER_PERMISSIONS, CacheKey::from('user:permission'));
    }

    public function test_enum_try_from_string()
    {
        $this->assertEquals(CacheKey::USER_PERMISSIONS, CacheKey::tryFrom('user:permission'));
        $this->assertNull(CacheKey::tryFrom('invalid'));
    }

    public function test_key_method()
    {
        $this->assertEquals('user:permission:123', CacheKey::USER_PERMISSIONS->key(123));
        $this->assertEquals('org_users:789', CacheKey::ORG_USERS->key(789));
    }

    public function test_key_method_with_zero()
    {
        $this->assertEquals('user:permission:0', CacheKey::USER_PERMISSIONS->key(0));
    }

    public function test_enum_from_invalid_string_throws_exception()
    {
        $this->expectException(\ValueError::class);
        CacheKey::from('invalid');
    }
}
