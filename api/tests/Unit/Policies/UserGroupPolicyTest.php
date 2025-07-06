<?php

namespace Tests\Unit\Policies;

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
    private User $groupMember;
    private UserGroup $userGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new UserGroupPolicy();
        $this->user = User::factory()->create();
        $this->groupMember = User::factory()->create();
        $this->userGroup = UserGroup::factory()->create();
        
        // Add groupMember to the userGroup
        $this->groupMember->userGroups()->attach($this->userGroup);
    }

    public function test_viewAny_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:viewany');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_viewAny_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_view_returns_true_when_user_has_viewAny_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:viewany');

        $this->assertTrue($this->policy->view($this->user, $this->userGroup));
    }

    public function test_view_returns_true_when_user_is_group_member_with_view_permission(): void
    {
        $this->giveUserPermission($this->groupMember, 'usergroup:view');

        $this->assertTrue($this->policy->view($this->groupMember, $this->userGroup));
    }

    public function test_view_returns_false_when_user_is_group_member_without_view_permission(): void
    {
        $this->assertFalse($this->policy->view($this->groupMember, $this->userGroup));
    }

    public function test_view_returns_false_when_user_is_not_group_member_and_lacks_viewAny_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:view');

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

    public function test_updateAny_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:updateany');

        $this->assertTrue($this->policy->updateAny($this->user));
    }

    public function test_updateAny_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->updateAny($this->user));
    }

    public function test_update_returns_true_when_user_has_updateAny_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:updateany');

        $this->assertTrue($this->policy->update($this->user, $this->userGroup));
    }

    public function test_update_returns_true_when_user_is_group_member_with_update_permission(): void
    {
        $this->giveUserPermission($this->groupMember, 'usergroup:update');

        $this->assertTrue($this->policy->update($this->groupMember, $this->userGroup));
    }

    public function test_update_returns_false_when_user_is_group_member_without_update_permission(): void
    {
        $this->assertFalse($this->policy->update($this->groupMember, $this->userGroup));
    }

    public function test_update_returns_false_when_user_is_not_group_member_and_lacks_updateAny_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:update');

        $this->assertFalse($this->policy->update($this->user, $this->userGroup));
    }

    public function test_deleteAny_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:deleteany');

        $this->assertTrue($this->policy->deleteAny($this->user));
    }

    public function test_deleteAny_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->deleteAny($this->user));
    }

    public function test_restoreAny_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:restoreany');

        $this->assertTrue($this->policy->restoreAny($this->user));
    }

    public function test_restoreAny_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_view_with_multiple_user_groups(): void
    {
        // Create another user group
        $anotherUserGroup = UserGroup::factory()->create();
        
        // User is member of first group but not second
        $this->giveUserPermission($this->groupMember, 'usergroup:view');

        // Should be able to view group they belong to
        $this->assertTrue($this->policy->view($this->groupMember, $this->userGroup));
        
        // Should not be able to view group they don't belong to
        $this->assertFalse($this->policy->view($this->groupMember, $anotherUserGroup));
    }

    public function test_update_with_multiple_user_groups(): void
    {
        // Create another user group
        $anotherUserGroup = UserGroup::factory()->create();
        
        // User is member of first group but not second
        $this->giveUserPermission($this->groupMember, 'usergroup:update');

        // Should be able to update group they belong to
        $this->assertTrue($this->policy->update($this->groupMember, $this->userGroup));
        
        // Should not be able to update group they don't belong to
        $this->assertFalse($this->policy->update($this->groupMember, $anotherUserGroup));
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