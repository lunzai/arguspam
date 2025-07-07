<?php

namespace Tests\Unit\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\PasswordPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    private PasswordPolicy $policy;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new PasswordPolicy;
        $this->user = User::factory()->create();
    }

    public function test_update_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'password:update');

        $this->assertTrue($this->policy->update($this->user));
    }

    public function test_update_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->update($this->user));
    }

    public function test_user_with_no_permissions_cannot_update(): void
    {
        $this->assertFalse($this->policy->update($this->user));
    }

    public function test_multiple_users_with_different_permissions(): void
    {
        $authorizedUser = User::factory()->create();
        $unauthorizedUser = User::factory()->create();

        $this->giveUserPermission($authorizedUser, 'password:update');

        $this->assertTrue($this->policy->update($authorizedUser));
        $this->assertFalse($this->policy->update($unauthorizedUser));
        $this->assertFalse($this->policy->update($this->user));
    }

    public function test_permission_names_are_case_insensitive(): void
    {
        $this->giveUserPermission($this->user, 'PASSWORD:UPDATE');

        $this->assertTrue($this->policy->update($this->user));
    }

    public function test_update_with_similar_but_different_permission_names(): void
    {
        // Test with similar but different permission names
        $this->giveUserPermission($this->user, 'password:create'); // Note: 'create' not 'update'

        $this->assertFalse($this->policy->update($this->user)); // Should not match
    }

    public function test_policy_handles_edge_cases(): void
    {
        // Test with permission that starts with the same prefix but is different
        $this->giveUserPermission($this->user, 'password:updated');

        $this->assertFalse($this->policy->update($this->user)); // Should not match
    }

    public function test_user_with_multiple_roles_and_permissions(): void
    {
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();

        $permission1 = Permission::firstOrCreate(
            ['name' => 'password:update'],
            ['description' => 'Update Password']
        );
        $permission2 = Permission::firstOrCreate(
            ['name' => 'some:other'],
            ['description' => 'Some Other Permission']
        );

        $role1->permissions()->attach($permission1);
        $role2->permissions()->attach($permission2);

        $this->user->roles()->attach([$role1->id, $role2->id]);
        $this->user->clearUserRolePermissionCache();

        $this->assertTrue($this->policy->update($this->user));
    }

    public function test_password_permission_is_independent_of_other_permissions(): void
    {
        // User with other permissions but not password should not have access
        $userWithOtherPerms = User::factory()->create();
        $this->giveUserPermission($userWithOtherPerms, 'user:view');
        $this->giveUserPermission($userWithOtherPerms, 'asset:view');

        $this->assertFalse($this->policy->update($userWithOtherPerms));

        // Different user with password permission should have access
        $userWithPassword = User::factory()->create();
        $this->giveUserPermission($userWithPassword, 'password:update');
        $this->assertTrue($this->policy->update($userWithPassword));
    }

    public function test_user_with_password_permission_only_has_update_access(): void
    {
        $this->giveUserPermission($this->user, 'password:update');

        $this->assertTrue($this->policy->update($this->user));

        // User should not have any other permissions
        $this->assertFalse($this->user->hasAnyPermission('user:view'));
        $this->assertFalse($this->user->hasAnyPermission('asset:view'));
        $this->assertFalse($this->user->hasAnyPermission('dashboard:viewany'));
    }

    public function test_policy_with_different_permission_variations(): void
    {
        // Test that only exact permission match works
        $variations = [
            'password:updates',
            'password:updating',
            'password:view',
            'password:create',
            'password:delete',
            'passwords:update',
            'user:password:update',
        ];

        foreach ($variations as $variation) {
            $testUser = User::factory()->create();
            $this->giveUserPermission($testUser, $variation);

            $this->assertFalse($this->policy->update($testUser), "Permission '{$variation}' should not grant update access");
        }
    }

    public function test_multiple_password_permissions_scenario(): void
    {
        // Give user multiple password-related permissions
        $this->giveUserPermission($this->user, 'password:update');
        $this->giveUserPermission($this->user, 'password:view');
        $this->giveUserPermission($this->user, 'password:create');

        // Should still return true for update
        $this->assertTrue($this->policy->update($this->user));

        // Verify user has multiple permissions
        $this->assertTrue($this->user->hasAnyPermission('password:view'));
        $this->assertTrue($this->user->hasAnyPermission('password:create'));
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
