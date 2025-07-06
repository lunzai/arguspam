<?php

namespace Tests\Unit\Rules;

use App\Enums\CacheKey;
use App\Models\Org;
use App\Models\User;
use App\Rules\UserExistedInOrg;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UserExistedInOrgTest extends TestCase
{
    use RefreshDatabase;

    protected $org;
    protected $user;
    protected $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        
        // Attach user to org
        $this->org->users()->attach($this->user);
    }

    public function test_rule_can_be_instantiated()
    {
        $rule = new UserExistedInOrg($this->org->id);
        
        $this->assertInstanceOf(UserExistedInOrg::class, $rule);
    }

    public function test_rule_implements_validation_rule_interface()
    {
        $this->assertTrue(in_array('Illuminate\Contracts\Validation\ValidationRule', class_implements(UserExistedInOrg::class)));
    }

    public function test_validates_successfully_when_user_exists_in_org()
    {
        $rule = new UserExistedInOrg($this->org->id);
        $failCalled = false;
        
        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };
        
        $rule->validate('user_id', $this->user->id, $fail);
        
        $this->assertFalse($failCalled);
    }

    public function test_fails_when_user_does_not_exist_in_org()
    {
        $rule = new UserExistedInOrg($this->org->id);
        $failCalled = false;
        $failMessage = '';
        
        $fail = function ($message) use (&$failCalled, &$failMessage) {
            $failCalled = true;
            $failMessage = $message;
        };
        
        $rule->validate('user_id', $this->otherUser->id, $fail);
        
        $this->assertTrue($failCalled);
        $this->assertEquals('The user does not belong to this organization.', $failMessage);
    }

    public function test_fails_when_user_id_is_invalid()
    {
        $rule = new UserExistedInOrg($this->org->id);
        $failCalled = false;
        
        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };
        
        $rule->validate('user_id', 99999, $fail);
        
        $this->assertTrue($failCalled);
    }

    public function test_fails_when_user_id_is_null()
    {
        $rule = new UserExistedInOrg($this->org->id);
        $failCalled = false;
        
        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };
        
        $rule->validate('user_id', null, $fail);
        
        $this->assertTrue($failCalled);
    }

    public function test_caching_is_used_for_org_users()
    {
        // Clear any existing cache first
        Cache::forget(CacheKey::ORG_USERS->key($this->org->id));
        
        $rule = new UserExistedInOrg($this->org->id);
        $failCalled = false;
        
        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };
        
        // First call should hit the database and cache the result
        $rule->validate('user_id', $this->user->id, $fail);
        $this->assertFalse($failCalled);
        
        // Verify the cache has been set
        $cachedUsers = Cache::get(CacheKey::ORG_USERS->key($this->org->id));
        $this->assertNotNull($cachedUsers);
        $this->assertTrue($cachedUsers->contains('id', $this->user->id));
    }

    public function test_cache_key_is_generated_correctly()
    {
        $expectedCacheKey = CacheKey::ORG_USERS->key($this->org->id);
        
        // Clear cache first
        Cache::forget($expectedCacheKey);
        
        $rule = new UserExistedInOrg($this->org->id);
        $rule->validate('user_id', $this->user->id, function() {});
        
        // Verify the cache was set with the expected key
        $this->assertTrue(Cache::has($expectedCacheKey));
        
        $cachedData = Cache::get($expectedCacheKey);
        $this->assertNotNull($cachedData);
        $this->assertTrue($cachedData->contains('id', $this->user->id));
    }

    public function test_validates_with_multiple_users_in_org()
    {
        $anotherUser = User::factory()->create();
        $this->org->users()->attach($anotherUser);
        
        $rule = new UserExistedInOrg($this->org->id);
        
        // Test first user
        $failCalled = false;
        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };
        
        $rule->validate('user_id', $this->user->id, $fail);
        $this->assertFalse($failCalled);
        
        // Clear cache for next test
        Cache::forget(CacheKey::ORG_USERS->key($this->org->id));
        
        // Test second user
        $failCalled = false;
        $rule->validate('user_id', $anotherUser->id, $fail);
        $this->assertFalse($failCalled);
    }

    public function test_handles_empty_org_users()
    {
        $emptyOrg = Org::factory()->create();
        $rule = new UserExistedInOrg($emptyOrg->id);
        $failCalled = false;
        
        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };
        
        $rule->validate('user_id', $this->user->id, $fail);
        
        $this->assertTrue($failCalled);
    }

    public function test_validates_with_string_user_id()
    {
        $rule = new UserExistedInOrg($this->org->id);
        $failCalled = false;
        
        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };
        
        $rule->validate('user_id', (string) $this->user->id, $fail);
        
        $this->assertFalse($failCalled);
    }

    public function test_validates_with_integer_user_id()
    {
        $rule = new UserExistedInOrg($this->org->id);
        $failCalled = false;
        
        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };
        
        $rule->validate('user_id', (int) $this->user->id, $fail);
        
        $this->assertFalse($failCalled);
    }

    public function test_error_message_is_correct()
    {
        $rule = new UserExistedInOrg($this->org->id);
        $actualMessage = '';
        
        $fail = function ($message) use (&$actualMessage) {
            $actualMessage = $message;
        };
        
        $rule->validate('user_id', $this->otherUser->id, $fail);
        
        $this->assertEquals('The user does not belong to this organization.', $actualMessage);
    }

    public function test_rule_constructor_accepts_org_id()
    {
        $rule = new UserExistedInOrg($this->org->id);
        
        $reflection = new \ReflectionClass($rule);
        $orgIdProperty = $reflection->getProperty('orgId');
        $orgIdProperty->setAccessible(true);
        
        $this->assertEquals($this->org->id, $orgIdProperty->getValue($rule));
    }

    public function test_throws_error_for_non_existent_org()
    {
        $nonExistentOrgId = 99999;
        $rule = new UserExistedInOrg($nonExistentOrgId);
        
        $fail = function ($message) {};
        
        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to a member function users() on null');
        
        $rule->validate('user_id', $this->user->id, $fail);
    }

    public function test_cache_is_reused_on_subsequent_calls()
    {
        // Clear cache first
        Cache::forget(CacheKey::ORG_USERS->key($this->org->id));
        
        $rule = new UserExistedInOrg($this->org->id);
        
        // First call
        $rule->validate('user_id', $this->user->id, function() {});
        
        // Verify cache is set
        $this->assertTrue(Cache::has(CacheKey::ORG_USERS->key($this->org->id)));
        
        // Second call should use cache
        $rule->validate('user_id', $this->user->id, function() {});
        
        // Cache should still be there
        $this->assertTrue(Cache::has(CacheKey::ORG_USERS->key($this->org->id)));
    }

    public function test_validates_with_zero_user_id()
    {
        $rule = new UserExistedInOrg($this->org->id);
        $failCalled = false;
        
        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };
        
        $rule->validate('user_id', 0, $fail);
        
        $this->assertTrue($failCalled);
    }

    public function test_validates_with_negative_user_id()
    {
        $rule = new UserExistedInOrg($this->org->id);
        $failCalled = false;
        
        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };
        
        $rule->validate('user_id', -1, $fail);
        
        $this->assertTrue($failCalled);
    }

    protected function tearDown(): void
    {
        // Clear specific cache keys used in tests
        if (isset($this->org)) {
            Cache::forget(CacheKey::ORG_USERS->key($this->org->id));
        }
        parent::tearDown();
    }
}