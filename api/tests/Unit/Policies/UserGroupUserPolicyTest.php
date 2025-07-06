<?php

namespace Tests\Unit\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\UserGroupUserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGroupUserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserGroupUserPolicy $policy;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new UserGroupUserPolicy();
        $this->user = User::factory()->create();
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroupuser:create');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    public function test_delete_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'usergroupuser:delete');

        $this->assertTrue($this->policy->delete($this->user));
    }

    public function test_delete_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->delete($this->user));
    }

    public function test_user_with_both_permissions_can_perform_both_actions(): void
    {
        $this->giveUserPermission($this->user, 'usergroupuser:create');
        $this->giveUserPermission($this->user, 'usergroupuser:delete');

        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->delete($this->user));
    }

    public function test_user_with_only_create_permission_cannot_delete(): void
    {
        $this->giveUserPermission($this->user, 'usergroupuser:create');

        $this->assertTrue($this->policy->create($this->user));
        $this->assertFalse($this->policy->delete($this->user));
    }

    public function test_user_with_only_delete_permission_cannot_create(): void
    {
        $this->giveUserPermission($this->user, 'usergroupuser:delete');

        $this->assertFalse($this->policy->create($this->user));
        $this->assertTrue($this->policy->delete($this->user));
    }

    public function test_user_with_no_permissions_cannot_perform_any_actions(): void
    {
        $this->assertFalse($this->policy->create($this->user));
        $this->assertFalse($this->policy->delete($this->user));
    }

    public function test_multiple_users_with_different_permissions(): void
    {
        $createUser = User::factory()->create();
        $deleteUser = User::factory()->create();
        $bothUser = User::factory()->create();

        $this->giveUserPermission($createUser, 'usergroupuser:create');
        $this->giveUserPermission($deleteUser, 'usergroupuser:delete');
        $this->giveUserPermission($bothUser, 'usergroupuser:create');
        $this->giveUserPermission($bothUser, 'usergroupuser:delete');

        // Create user
        $this->assertTrue($this->policy->create($createUser));
        $this->assertFalse($this->policy->delete($createUser));

        // Delete user
        $this->assertFalse($this->policy->create($deleteUser));
        $this->assertTrue($this->policy->delete($deleteUser));

        // Both permissions user
        $this->assertTrue($this->policy->create($bothUser));
        $this->assertTrue($this->policy->delete($bothUser));

        // No permissions user
        $this->assertFalse($this->policy->create($this->user));
        $this->assertFalse($this->policy->delete($this->user));
    }

    public function test_permission_names_are_case_insensitive(): void
    {
        $this->giveUserPermission($this->user, 'USERGROUPUSER:CREATE');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_and_delete_are_independent_operations(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Give different permissions to different users
        $this->giveUserPermission($user1, 'usergroupuser:create');
        $this->giveUserPermission($user2, 'usergroupuser:delete');

        // Each user can only perform the action they have permission for
        $this->assertTrue($this->policy->create($user1));
        $this->assertFalse($this->policy->delete($user1));

        $this->assertFalse($this->policy->create($user2));
        $this->assertTrue($this->policy->delete($user2));
    }

    public function test_policy_handles_permission_edge_cases(): void
    {
        // Test with similar but different permission names
        $this->giveUserPermission($this->user, 'usergroupuser:created'); // Note: 'created' not 'create'

        $this->assertFalse($this->policy->create($this->user)); // Should not match
        $this->assertFalse($this->policy->delete($this->user));
    }

    public function test_policy_methods_are_independent(): void
    {
        // Test that create and delete permissions are completely independent
        $createOnlyUser = User::factory()->create();
        $deleteOnlyUser = User::factory()->create();
        
        $this->giveUserPermission($createOnlyUser, 'usergroupuser:create');
        $this->giveUserPermission($deleteOnlyUser, 'usergroupuser:delete');
        
        // Create-only user
        $this->assertTrue($this->policy->create($createOnlyUser));
        $this->assertFalse($this->policy->delete($createOnlyUser));
        
        // Delete-only user  
        $this->assertFalse($this->policy->create($deleteOnlyUser));
        $this->assertTrue($this->policy->delete($deleteOnlyUser));
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