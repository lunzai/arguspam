<?php

namespace Tests\Unit\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\UserRolePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRolePolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserRolePolicy $policy;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new UserRolePolicy;
        $this->user = User::factory()->create();
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'userrole:create');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    public function test_delete_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'userrole:delete');

        $this->assertTrue($this->policy->delete($this->user));
    }

    public function test_delete_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->delete($this->user));
    }

    public function test_user_with_both_permissions_can_perform_both_actions(): void
    {
        $this->giveUserPermission($this->user, 'userrole:create');
        $this->giveUserPermission($this->user, 'userrole:delete');

        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->delete($this->user));
    }

    public function test_user_with_only_create_permission_cannot_delete(): void
    {
        $this->giveUserPermission($this->user, 'userrole:create');

        $this->assertTrue($this->policy->create($this->user));
        $this->assertFalse($this->policy->delete($this->user));
    }

    public function test_user_with_only_delete_permission_cannot_create(): void
    {
        $this->giveUserPermission($this->user, 'userrole:delete');

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

        $this->giveUserPermission($createUser, 'userrole:create');
        $this->giveUserPermission($deleteUser, 'userrole:delete');
        $this->giveUserPermission($bothUser, 'userrole:create');
        $this->giveUserPermission($bothUser, 'userrole:delete');

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
        $this->giveUserPermission($this->user, 'USERROLE:CREATE');

        $this->assertTrue($this->policy->create($this->user));
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
