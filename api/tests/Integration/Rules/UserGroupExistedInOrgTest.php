<?php

namespace Tests\Integration\Rules;

use App\Models\Org;
use App\Models\UserGroup;
use App\Rules\UserGroupExistedInOrg;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGroupExistedInOrgTest extends TestCase
{
    use RefreshDatabase;

    protected $org;
    protected $userGroup;
    protected $otherUserGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Org::factory()->create();
        $this->userGroup = UserGroup::factory()->create();
        $this->otherUserGroup = UserGroup::factory()->create();

        // Set user group to belong to org
        $this->userGroup->org_id = $this->org->id;
        $this->userGroup->save();
    }

    public function test_rule_can_be_instantiated(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);

        $this->assertInstanceOf(UserGroupExistedInOrg::class, $rule);
    }

    public function test_rule_implements_validation_rule_interface(): void
    {
        $this->assertTrue(
            in_array('Illuminate\Contracts\Validation\ValidationRule', class_implements(UserGroupExistedInOrg::class))
        );
    }

    public function test_constructor_sets_org_id_correctly(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);

        $reflection = new \ReflectionClass($rule);
        $orgIdProperty = $reflection->getProperty('orgId');
        $orgIdProperty->setAccessible(true);

        $this->assertEquals($this->org->id, $orgIdProperty->getValue($rule));
    }

    public function test_validates_successfully_when_user_group_exists_in_org(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', $this->userGroup->id, $fail);

        $this->assertFalse($failCalled);
    }

    public function test_fails_when_user_group_does_not_exist_in_org(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);
        $failCalled = false;
        $failMessage = '';

        $fail = function ($message) use (&$failCalled, &$failMessage) {
            $failCalled = true;
            $failMessage = $message;
        };

        $rule->validate('user_group_id', $this->otherUserGroup->id, $fail);

        $this->assertTrue($failCalled);
        $this->assertEquals('The user group does not belong to this organization.', $failMessage);
    }

    public function test_fails_when_user_group_id_is_invalid(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', 99999, $fail);

        $this->assertTrue($failCalled);
    }

    public function test_fails_when_user_group_id_is_null(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', null, $fail);

        $this->assertTrue($failCalled);
    }

    public function test_fails_when_user_group_id_is_zero(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', 0, $fail);

        $this->assertTrue($failCalled);
    }

    public function test_fails_when_user_group_id_is_negative(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', -1, $fail);

        $this->assertTrue($failCalled);
    }

    public function test_validates_with_string_user_group_id(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', (string) $this->userGroup->id, $fail);

        $this->assertFalse($failCalled);
    }

    public function test_validates_with_integer_user_group_id(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', (int) $this->userGroup->id, $fail);

        $this->assertFalse($failCalled);
    }

    public function test_validates_with_multiple_user_groups_in_org(): void
    {
        $anotherUserGroup = UserGroup::factory()->create();
        $anotherUserGroup->org_id = $this->org->id;
        $anotherUserGroup->save();

        $rule = new UserGroupExistedInOrg($this->org->id);

        // Test first user group
        $failCalled = false;
        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', $this->userGroup->id, $fail);
        $this->assertFalse($failCalled);

        // Test second user group
        $failCalled = false;
        $rule->validate('user_group_id', $anotherUserGroup->id, $fail);
        $this->assertFalse($failCalled);
    }

    public function test_handles_empty_org_user_groups(): void
    {
        $emptyOrg = Org::factory()->create();
        $rule = new UserGroupExistedInOrg($emptyOrg->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', $this->userGroup->id, $fail);

        $this->assertTrue($failCalled);
    }

    public function test_error_message_is_correct(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);
        $actualMessage = '';

        $fail = function ($message) use (&$actualMessage) {
            $actualMessage = $message;
        };

        $rule->validate('user_group_id', $this->otherUserGroup->id, $fail);

        $this->assertEquals('The user group does not belong to this organization.', $actualMessage);
    }

    public function test_throws_error_for_non_existent_org(): void
    {
        $nonExistentOrgId = 99999;
        $rule = new UserGroupExistedInOrg($nonExistentOrgId);

        $fail = function ($message) {};

        $this->expectException(\Error::class);
        $this->expectExceptionMessage('Call to a member function userGroups() on null');

        $rule->validate('user_group_id', $this->userGroup->id, $fail);
    }

    public function test_handles_float_user_group_id(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', (float) $this->userGroup->id, $fail);

        $this->assertFalse($failCalled);
    }

    public function test_handles_boolean_user_group_id(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', true, $fail);

        $this->assertTrue($failCalled);
    }

    public function test_handles_empty_string_user_group_id(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', '', $fail);

        $this->assertTrue($failCalled);
    }

    public function test_handles_very_large_user_group_id(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', PHP_INT_MAX, $fail);

        $this->assertTrue($failCalled);
    }

    public function test_handles_very_small_user_group_id(): void
    {
        $rule = new UserGroupExistedInOrg($this->org->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', PHP_INT_MIN, $fail);

        $this->assertTrue($failCalled);
    }

    public function test_handles_string_org_id_in_constructor(): void
    {
        $rule = new UserGroupExistedInOrg((string) $this->org->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', $this->userGroup->id, $fail);

        $this->assertFalse($failCalled);
    }

    public function test_handles_float_org_id_in_constructor(): void
    {
        $rule = new UserGroupExistedInOrg((float) $this->org->id);
        $failCalled = false;

        $fail = function ($message) use (&$failCalled) {
            $failCalled = true;
        };

        $rule->validate('user_group_id', $this->userGroup->id, $fail);

        $this->assertFalse($failCalled);
    }

}
