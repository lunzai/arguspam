<?php

namespace Tests\Unit\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\RolePermissionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionPolicyTest extends TestCase
{
    use RefreshDatabase;

    private RolePermissionPolicy $policy;
    private User $user;
    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new RolePermissionPolicy;
        $this->user = User::factory()->create();
        $this->role = Role::factory()->create();
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'rolepermission:create');

        $this->assertTrue($this->policy->create($this->user, $this->role));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user, $this->role));
    }

    public function test_delete_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'rolepermission:delete');

        $this->assertTrue($this->policy->delete($this->user, $this->role));
    }

    public function test_delete_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->delete($this->user, $this->role));
    }

    public function test_user_with_both_permissions_can_perform_both_actions(): void
    {
        $this->giveUserPermission($this->user, 'rolepermission:create');
        $this->giveUserPermission($this->user, 'rolepermission:delete');

        $this->assertTrue($this->policy->create($this->user, $this->role));
        $this->assertTrue($this->policy->delete($this->user, $this->role));
    }

    public function test_user_with_only_create_permission_cannot_delete(): void
    {
        $this->giveUserPermission($this->user, 'rolepermission:create');

        $this->assertTrue($this->policy->create($this->user, $this->role));
        $this->assertFalse($this->policy->delete($this->user, $this->role));
    }

    public function test_user_with_only_delete_permission_cannot_create(): void
    {
        $this->giveUserPermission($this->user, 'rolepermission:delete');

        $this->assertFalse($this->policy->create($this->user, $this->role));
        $this->assertTrue($this->policy->delete($this->user, $this->role));
    }

    public function test_user_with_no_permissions_cannot_perform_any_actions(): void
    {
        $this->assertFalse($this->policy->create($this->user, $this->role));
        $this->assertFalse($this->policy->delete($this->user, $this->role));
    }

    public function test_multiple_users_with_different_permissions(): void
    {
        $createUser = User::factory()->create();
        $deleteUser = User::factory()->create();
        $bothUser = User::factory()->create();

        $this->giveUserPermission($createUser, 'rolepermission:create');
        $this->giveUserPermission($deleteUser, 'rolepermission:delete');
        $this->giveUserPermission($bothUser, 'rolepermission:create');
        $this->giveUserPermission($bothUser, 'rolepermission:delete');

        // Create user
        $this->assertTrue($this->policy->create($createUser, $this->role));
        $this->assertFalse($this->policy->delete($createUser, $this->role));

        // Delete user
        $this->assertFalse($this->policy->create($deleteUser, $this->role));
        $this->assertTrue($this->policy->delete($deleteUser, $this->role));

        // Both permissions user
        $this->assertTrue($this->policy->create($bothUser, $this->role));
        $this->assertTrue($this->policy->delete($bothUser, $this->role));

        // No permissions user
        $this->assertFalse($this->policy->create($this->user, $this->role));
        $this->assertFalse($this->policy->delete($this->user, $this->role));
    }

    public function test_role_parameter_is_accepted_but_not_used_for_authorization(): void
    {
        // The current implementation doesn't use the $role parameter for authorization
        // It only checks if user has the permission regardless of the role
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();

        $this->giveUserPermission($this->user, 'rolepermission:create');

        // Should return same result for any role
        $this->assertTrue($this->policy->create($this->user, $role1));
        $this->assertTrue($this->policy->create($this->user, $role2));
        $this->assertTrue($this->policy->create($this->user, $this->role));
    }

    public function test_permission_names_are_case_insensitive(): void
    {
        $this->giveUserPermission($this->user, 'ROLEPERMISSION:CREATE');

        $this->assertTrue($this->policy->create($this->user, $this->role));
    }

    public function test_create_and_delete_are_independent_operations(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Give different permissions to different users
        $this->giveUserPermission($user1, 'rolepermission:create');
        $this->giveUserPermission($user2, 'rolepermission:delete');

        // Each user can only perform the action they have permission for
        $this->assertTrue($this->policy->create($user1, $this->role));
        $this->assertFalse($this->policy->delete($user1, $this->role));

        $this->assertFalse($this->policy->create($user2, $this->role));
        $this->assertTrue($this->policy->delete($user2, $this->role));
    }

    public function test_policy_handles_permission_edge_cases(): void
    {
        // Test with similar but different permission names
        $this->giveUserPermission($this->user, 'rolepermission:created'); // Note: 'created' not 'create'

        $this->assertFalse($this->policy->create($this->user, $this->role)); // Should not match
        $this->assertFalse($this->policy->delete($this->user, $this->role));
    }

    public function test_policy_methods_are_independent(): void
    {
        // Test that create and delete permissions are completely independent
        $createOnlyUser = User::factory()->create();
        $deleteOnlyUser = User::factory()->create();

        $this->giveUserPermission($createOnlyUser, 'rolepermission:create');
        $this->giveUserPermission($deleteOnlyUser, 'rolepermission:delete');

        // Create-only user
        $this->assertTrue($this->policy->create($createOnlyUser, $this->role));
        $this->assertFalse($this->policy->delete($createOnlyUser, $this->role));

        // Delete-only user
        $this->assertFalse($this->policy->create($deleteOnlyUser, $this->role));
        $this->assertTrue($this->policy->delete($deleteOnlyUser, $this->role));
    }

    public function test_policy_with_different_permission_variations(): void
    {
        // Test that only exact permission match works
        $variations = [
            'rolepermission:creates',
            'rolepermission:view',
            'rolepermission:update',
            'role:permission:create',
            'permission:create',
            'rolepermissions:create',
        ];

        foreach ($variations as $variation) {
            $testUser = User::factory()->create();
            $this->giveUserPermission($testUser, $variation);

            $this->assertFalse($this->policy->create($testUser, $this->role), "Permission '{$variation}' should not grant create access");
            $this->assertFalse($this->policy->delete($testUser, $this->role), "Permission '{$variation}' should not grant delete access");
        }
    }

    public function test_user_with_multiple_roles_and_permissions(): void
    {
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();

        $permission1 = Permission::firstOrCreate(
            ['name' => 'rolepermission:create'],
            ['description' => 'Create Role Permission']
        );
        $permission2 = Permission::firstOrCreate(
            ['name' => 'rolepermission:delete'],
            ['description' => 'Delete Role Permission']
        );

        $role1->permissions()->attach($permission1);
        $role2->permissions()->attach($permission2);

        $this->user->roles()->attach([$role1->id, $role2->id]);
        $this->user->clearUserRolePermissionCache();

        $this->assertTrue($this->policy->create($this->user, $this->role));
        $this->assertTrue($this->policy->delete($this->user, $this->role));
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
