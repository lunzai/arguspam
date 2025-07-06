<?php

namespace Tests\Unit\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserPolicy $policy;
    private User $user;
    private User $targetUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new UserPolicy();
        $this->user = User::factory()->create();
        $this->targetUser = User::factory()->create();
    }

    public function test_viewAny_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:viewany');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_viewAny_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_view_returns_true_when_user_has_viewAny_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:viewany');

        $this->assertTrue($this->policy->view($this->user, $this->targetUser));
    }

    public function test_view_returns_true_when_viewing_own_profile_with_view_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:view');

        $this->assertTrue($this->policy->view($this->user, $this->user));
    }

    public function test_view_returns_false_when_viewing_own_profile_without_view_permission(): void
    {
        $this->assertFalse($this->policy->view($this->user, $this->user));
    }

    public function test_view_returns_false_when_viewing_other_user_without_viewAny_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:view');

        $this->assertFalse($this->policy->view($this->user, $this->targetUser));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:create');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    public function test_updateAny_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:updateany');

        $this->assertTrue($this->policy->updateAny($this->user));
    }

    public function test_updateAny_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->updateAny($this->user));
    }

    public function test_update_returns_true_when_user_has_updateAny_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:updateany');

        $this->assertTrue($this->policy->update($this->user, $this->targetUser));
    }

    public function test_update_returns_true_when_updating_own_profile_with_update_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:update');

        $this->assertTrue($this->policy->update($this->user, $this->user));
    }

    public function test_update_returns_false_when_updating_own_profile_without_update_permission(): void
    {
        $this->assertFalse($this->policy->update($this->user, $this->user));
    }

    public function test_update_returns_false_when_updating_other_user_without_updateAny_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:update');

        $this->assertFalse($this->policy->update($this->user, $this->targetUser));
    }

    public function test_deleteAny_returns_true_when_user_has_permission_and_not_deleting_self(): void
    {
        $this->giveUserPermission($this->user, 'user:deleteany');

        $this->assertTrue($this->policy->deleteAny($this->user, $this->targetUser));
    }

    public function test_deleteAny_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->deleteAny($this->user, $this->targetUser));
    }

    public function test_deleteAny_returns_false_when_user_tries_to_delete_self(): void
    {
        $this->giveUserPermission($this->user, 'user:deleteany');

        $this->assertFalse($this->policy->deleteAny($this->user, $this->user));
    }

    public function test_deleteAny_returns_false_when_user_tries_to_delete_self_even_without_permission(): void
    {
        $this->assertFalse($this->policy->deleteAny($this->user, $this->user));
    }

    public function test_restoreAny_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:restoreany');

        $this->assertTrue($this->policy->restoreAny($this->user));
    }

    public function test_restoreAny_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_user_with_multiple_permissions_can_perform_corresponding_actions(): void
    {
        $this->giveUserPermission($this->user, 'user:viewany');
        $this->giveUserPermission($this->user, 'user:updateany');
        $this->giveUserPermission($this->user, 'user:deleteany');

        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->view($this->user, $this->targetUser));
        $this->assertTrue($this->policy->updateAny($this->user));
        $this->assertTrue($this->policy->update($this->user, $this->targetUser));
        $this->assertTrue($this->policy->deleteAny($this->user, $this->targetUser));
        
        // Still cannot delete self
        $this->assertFalse($this->policy->deleteAny($this->user, $this->user));
    }

    public function test_user_with_only_self_permissions_cannot_act_on_others(): void
    {
        $this->giveUserPermission($this->user, 'user:view');
        $this->giveUserPermission($this->user, 'user:update');

        // Can act on self
        $this->assertTrue($this->policy->view($this->user, $this->user));
        $this->assertTrue($this->policy->update($this->user, $this->user));

        // Cannot act on others
        $this->assertFalse($this->policy->view($this->user, $this->targetUser));
        $this->assertFalse($this->policy->update($this->user, $this->targetUser));
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