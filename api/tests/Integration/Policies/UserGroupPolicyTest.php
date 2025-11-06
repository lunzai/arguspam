<?php

namespace Tests\Integration\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use App\Policies\UserGroupPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGroupPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserGroupPolicy $policy;
    private User $user;
    private UserGroup $userGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new UserGroupPolicy;
        $this->user = User::factory()->create();
        $this->userGroup = UserGroup::factory()->create();
    }

    public function test_view_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:view');

        $this->assertTrue($this->policy->view($this->user, $this->userGroup));
    }

    public function test_view_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->view($this->user, $this->userGroup));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:create');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    public function test_update_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:update');

        $this->assertTrue($this->policy->update($this->user, $this->userGroup));
    }

    public function test_update_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->update($this->user, $this->userGroup));
    }

    public function test_delete_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:delete');

        $this->assertTrue($this->policy->delete($this->user, $this->userGroup));
    }

    public function test_delete_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->delete($this->user, $this->userGroup));
    }

    public function test_add_user_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:adduser');

        $this->assertTrue($this->policy->addUser($this->user, $this->userGroup));
    }

    public function test_add_user_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->addUser($this->user, $this->userGroup));
    }

    public function test_remove_user_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:removeuser');

        $this->assertTrue($this->policy->removeUser($this->user, $this->userGroup));
    }

    public function test_remove_user_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->removeUser($this->user, $this->userGroup));
    }

    public function test_user_with_multiple_permissions_can_perform_all_actions(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:view');
        $this->giveUserPermission($this->user, 'usergroup:create');
        $this->giveUserPermission($this->user, 'usergroup:update');
        $this->giveUserPermission($this->user, 'usergroup:delete');
        $this->giveUserPermission($this->user, 'usergroup:adduser');
        $this->giveUserPermission($this->user, 'usergroup:removeuser');

        $this->assertTrue($this->policy->view($this->user, $this->userGroup));
        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->update($this->user, $this->userGroup));
        $this->assertTrue($this->policy->delete($this->user, $this->userGroup));
        $this->assertTrue($this->policy->addUser($this->user, $this->userGroup));
        $this->assertTrue($this->policy->removeUser($this->user, $this->userGroup));
    }

    public function test_user_with_no_permissions_cannot_perform_any_actions(): void
    {
        $this->assertFalse($this->policy->view($this->user, $this->userGroup));
        $this->assertFalse($this->policy->create($this->user));
        $this->assertFalse($this->policy->update($this->user, $this->userGroup));
        $this->assertFalse($this->policy->delete($this->user, $this->userGroup));
        $this->assertFalse($this->policy->addUser($this->user, $this->userGroup));
        $this->assertFalse($this->policy->removeUser($this->user, $this->userGroup));
    }

    private function giveUserPermission(User $user, string $permissionName): void
    {
        $permission = Permission::firstOrCreate(
            ['name' => $permissionName],
            ['description' => ucfirst(str_replace(':', ' ', $permissionName))]
        );
        $role = Role::factory()->create();
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);
        $user->clearUserRolePermissionCache();
    }
}
