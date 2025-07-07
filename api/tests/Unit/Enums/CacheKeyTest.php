<?php

namespace Tests\Unit\Enums;

use App\Enums\CacheKey;
use PHPUnit\Framework\TestCase;

class CacheKeyTest extends TestCase
{
    public function test_enum_cases_exist()
    {
        $cases = CacheKey::cases();
        $this->assertCount(8, $cases);

        $this->assertContains(CacheKey::USER_PERMISSIONS, $cases);
        $this->assertContains(CacheKey::USER_ROLES, $cases);
        $this->assertContains(CacheKey::ORG_USERS, $cases);
        $this->assertContains(CacheKey::SETTING_VALUE, $cases);
        $this->assertContains(CacheKey::SETTING_KEY, $cases);
        $this->assertContains(CacheKey::SETTING_ALL, $cases);
        $this->assertContains(CacheKey::SETTING_GROUP, $cases);
        $this->assertContains(CacheKey::SETTING_GROUP_ALL, $cases);
    }

    public function test_enum_values()
    {
        $this->assertEquals('user_permissions', CacheKey::USER_PERMISSIONS->value);
        $this->assertEquals('user_roles', CacheKey::USER_ROLES->value);
        $this->assertEquals('org_users', CacheKey::ORG_USERS->value);
        $this->assertEquals('settings:value', CacheKey::SETTING_VALUE->value);
        $this->assertEquals('settings:key', CacheKey::SETTING_KEY->value);
        $this->assertEquals('settings:all', CacheKey::SETTING_ALL->value);
        $this->assertEquals('settings:group', CacheKey::SETTING_GROUP->value);
        $this->assertEquals('settings:group:all', CacheKey::SETTING_GROUP_ALL->value);
    }

    public function test_enum_from_string()
    {
        $this->assertEquals(CacheKey::USER_PERMISSIONS, CacheKey::from('user_permissions'));
        $this->assertEquals(CacheKey::SETTING_VALUE, CacheKey::from('settings:value'));
        $this->assertEquals(CacheKey::SETTING_GROUP_ALL, CacheKey::from('settings:group:all'));
    }

    public function test_enum_try_from_string()
    {
        $this->assertEquals(CacheKey::USER_PERMISSIONS, CacheKey::tryFrom('user_permissions'));
        $this->assertEquals(CacheKey::SETTING_VALUE, CacheKey::tryFrom('settings:value'));
        $this->assertNull(CacheKey::tryFrom('invalid'));
    }

    public function test_key_method()
    {
        $this->assertEquals('user_permissions:123', CacheKey::USER_PERMISSIONS->key(123));
        $this->assertEquals('settings:value:456', CacheKey::SETTING_VALUE->key(456));
        $this->assertEquals('org_users:789', CacheKey::ORG_USERS->key(789));
    }

    public function test_key_method_with_zero()
    {
        $this->assertEquals('user_permissions:0', CacheKey::USER_PERMISSIONS->key(0));
    }

    public function test_enum_from_invalid_string_throws_exception()
    {
        $this->expectException(\ValueError::class);
        CacheKey::from('invalid');
    }
}
