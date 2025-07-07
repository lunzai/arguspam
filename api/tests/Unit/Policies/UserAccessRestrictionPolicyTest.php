<?php

namespace Tests\Unit\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\UserAccessRestrictionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccessRestrictionPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserAccessRestrictionPolicy $policy;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new UserAccessRestrictionPolicy;
        $this->user = User::factory()->create();
    }

    public function test_view_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'useraccessrestriction:viewany');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'useraccessrestriction:create');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    public function test_update_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'useraccessrestriction:updateany');

        $this->assertTrue($this->policy->updateAny($this->user));
    }

    public function test_update_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->updateAny($this->user));
    }

    public function test_delete_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'useraccessrestriction:deleteany');

        $this->assertTrue($this->policy->deleteAny($this->user));
    }

    public function test_delete_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->deleteAny($this->user));
    }

    public function test_restore_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'useraccessrestriction:restoreany');

        $this->assertTrue($this->policy->restoreAny($this->user));
    }

    public function test_restore_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_user_with_all_permissions_can_perform_all_actions(): void
    {
        $permissions = [
            'useraccessrestriction:viewany',
            'useraccessrestriction:create',
            'useraccessrestriction:updateany',
            'useraccessrestriction:deleteany',
            'useraccessrestriction:restoreany',
        ];

        foreach ($permissions as $permission) {
            $this->giveUserPermission($this->user, $permission);
        }

        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->updateAny($this->user));
        $this->assertTrue($this->policy->deleteAny($this->user));
        $this->assertTrue($this->policy->restoreAny($this->user));
    }

    public function test_user_with_no_permissions_cannot_perform_any_actions(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->create($this->user));
        $this->assertFalse($this->policy->updateAny($this->user));
        $this->assertFalse($this->policy->deleteAny($this->user));
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_multiple_users_with_different_permissions(): void
    {
        $viewAnyUser = User::factory()->create();
        $createUser = User::factory()->create();
        $updateUser = User::factory()->create();
        $deleteUser = User::factory()->create();
        $restoreUser = User::factory()->create();
        $allPermissionsUser = User::factory()->create();

        $this->giveUserPermission($viewAnyUser, 'useraccessrestriction:viewany');
        $this->giveUserPermission($createUser, 'useraccessrestriction:create');
        $this->giveUserPermission($updateUser, 'useraccessrestriction:updateany');
        $this->giveUserPermission($deleteUser, 'useraccessrestriction:deleteany');
        $this->giveUserPermission($restoreUser, 'useraccessrestriction:restoreany');

        $allPermissions = [
            'useraccessrestriction:viewany',
            'useraccessrestriction:create',
            'useraccessrestriction:updateany',
            'useraccessrestriction:deleteany',
            'useraccessrestriction:restoreany',
        ];
        foreach ($allPermissions as $permission) {
            $this->giveUserPermission($allPermissionsUser, $permission);
        }

        // ViewAny user
        $this->assertTrue($this->policy->viewAny($viewAnyUser));
        $this->assertFalse($this->policy->create($viewAnyUser));
        $this->assertFalse($this->policy->updateAny($viewAnyUser));
        $this->assertFalse($this->policy->deleteAny($viewAnyUser));
        $this->assertFalse($this->policy->restoreAny($viewAnyUser));

        // Create user
        $this->assertFalse($this->policy->viewAny($createUser));
        $this->assertTrue($this->policy->create($createUser));
        $this->assertFalse($this->policy->updateAny($createUser));
        $this->assertFalse($this->policy->deleteAny($createUser));
        $this->assertFalse($this->policy->restoreAny($createUser));

        // Update user
        $this->assertFalse($this->policy->viewAny($updateUser));
        $this->assertFalse($this->policy->create($updateUser));
        $this->assertTrue($this->policy->updateAny($updateUser));
        $this->assertFalse($this->policy->deleteAny($updateUser));
        $this->assertFalse($this->policy->restoreAny($updateUser));

        // Delete user
        $this->assertFalse($this->policy->viewAny($deleteUser));
        $this->assertFalse($this->policy->create($deleteUser));
        $this->assertFalse($this->policy->updateAny($deleteUser));
        $this->assertTrue($this->policy->deleteAny($deleteUser));
        $this->assertFalse($this->policy->restoreAny($deleteUser));

        // Restore user
        $this->assertFalse($this->policy->viewAny($restoreUser));
        $this->assertFalse($this->policy->create($restoreUser));
        $this->assertFalse($this->policy->updateAny($restoreUser));
        $this->assertFalse($this->policy->deleteAny($restoreUser));
        $this->assertTrue($this->policy->restoreAny($restoreUser));

        // All permissions user
        $this->assertTrue($this->policy->viewAny($allPermissionsUser));
        $this->assertTrue($this->policy->create($allPermissionsUser));
        $this->assertTrue($this->policy->updateAny($allPermissionsUser));
        $this->assertTrue($this->policy->deleteAny($allPermissionsUser));
        $this->assertTrue($this->policy->restoreAny($allPermissionsUser));

        // No permissions user
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->create($this->user));
        $this->assertFalse($this->policy->updateAny($this->user));
        $this->assertFalse($this->policy->deleteAny($this->user));
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_permission_names_are_case_insensitive(): void
    {
        $this->giveUserPermission($this->user, 'USERACCESSRESTRICTION:VIEWANY');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_permissions_are_independent(): void
    {
        // Each permission should only grant access to its specific action
        $this->giveUserPermission($this->user, 'useraccessrestriction:create');

        $this->assertTrue($this->policy->create($this->user));
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->updateAny($this->user));
        $this->assertFalse($this->policy->deleteAny($this->user));
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_policy_handles_permission_edge_cases(): void
    {
        // Test with similar but different permission names
        $this->giveUserPermission($this->user, 'useraccessrestriction:created'); // Note: 'created' not 'create'

        $this->assertFalse($this->policy->create($this->user)); // Should not match
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->updateAny($this->user));
        $this->assertFalse($this->policy->deleteAny($this->user));
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_operations_are_independent(): void
    {
        // Test that operations are completely independent
        $createOnlyUser = User::factory()->create();
        $updateOnlyUser = User::factory()->create();
        $deleteOnlyUser = User::factory()->create();
        $restoreOnlyUser = User::factory()->create();

        $this->giveUserPermission($createOnlyUser, 'useraccessrestriction:create');
        $this->giveUserPermission($updateOnlyUser, 'useraccessrestriction:updateany');
        $this->giveUserPermission($deleteOnlyUser, 'useraccessrestriction:deleteany');
        $this->giveUserPermission($restoreOnlyUser, 'useraccessrestriction:restoreany');

        // Create-only user
        $this->assertTrue($this->policy->create($createOnlyUser));
        $this->assertFalse($this->policy->viewAny($createOnlyUser));
        $this->assertFalse($this->policy->updateAny($createOnlyUser));
        $this->assertFalse($this->policy->deleteAny($createOnlyUser));
        $this->assertFalse($this->policy->restoreAny($createOnlyUser));

        // Update-only user
        $this->assertFalse($this->policy->create($updateOnlyUser));
        $this->assertFalse($this->policy->viewAny($updateOnlyUser));
        $this->assertTrue($this->policy->updateAny($updateOnlyUser));
        $this->assertFalse($this->policy->deleteAny($updateOnlyUser));
        $this->assertFalse($this->policy->restoreAny($updateOnlyUser));

        // Delete-only user
        $this->assertFalse($this->policy->create($deleteOnlyUser));
        $this->assertFalse($this->policy->viewAny($deleteOnlyUser));
        $this->assertFalse($this->policy->updateAny($deleteOnlyUser));
        $this->assertTrue($this->policy->deleteAny($deleteOnlyUser));
        $this->assertFalse($this->policy->restoreAny($deleteOnlyUser));

        // Restore-only user
        $this->assertFalse($this->policy->create($restoreOnlyUser));
        $this->assertFalse($this->policy->viewAny($restoreOnlyUser));
        $this->assertFalse($this->policy->updateAny($restoreOnlyUser));
        $this->assertFalse($this->policy->deleteAny($restoreOnlyUser));
        $this->assertTrue($this->policy->restoreAny($restoreOnlyUser));
    }

    public function test_policy_with_different_permission_variations(): void
    {
        // Test that only exact permission match works
        $variations = [
            'useraccessrestriction:view',
            'useraccessrestriction:update',
            'useraccessrestriction:delete',
            'useraccessrestriction:restore',
            'useraccessrestrictions:viewany',
            'user:accessrestriction:viewany',
            'access:restriction:viewany',
        ];

        foreach ($variations as $variation) {
            $testUser = User::factory()->create();
            $this->giveUserPermission($testUser, $variation);

            $this->assertFalse($this->policy->viewAny($testUser), "Permission '{$variation}' should not grant viewAny access");
            $this->assertFalse($this->policy->create($testUser), "Permission '{$variation}' should not grant create access");
            $this->assertFalse($this->policy->updateAny($testUser), "Permission '{$variation}' should not grant updateAny access");
            $this->assertFalse($this->policy->deleteAny($testUser), "Permission '{$variation}' should not grant deleteAny access");
            $this->assertFalse($this->policy->restoreAny($testUser), "Permission '{$variation}' should not grant restoreAny access");
        }
    }

    public function test_user_with_multiple_roles_and_permissions(): void
    {
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();
        $role3 = Role::factory()->create();

        $permission1 = Permission::firstOrCreate(
            ['name' => 'useraccessrestriction:viewany'],
            ['description' => 'View Any User Access Restriction']
        );
        $permission2 = Permission::firstOrCreate(
            ['name' => 'useraccessrestriction:create'],
            ['description' => 'Create User Access Restriction']
        );
        $permission3 = Permission::firstOrCreate(
            ['name' => 'useraccessrestriction:deleteany'],
            ['description' => 'Delete Any User Access Restriction']
        );

        $role1->permissions()->attach($permission1);
        $role2->permissions()->attach($permission2);
        $role3->permissions()->attach($permission3);

        $this->user->roles()->attach([$role1->id, $role2->id, $role3->id]);
        $this->user->clearUserRolePermissionCache();

        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->create($this->user));
        $this->assertFalse($this->policy->updateAny($this->user));
        $this->assertTrue($this->policy->deleteAny($this->user));
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_permission_hierarchy_independence(): void
    {
        // Test that having higher-level permissions doesn't automatically grant lower-level ones
        $this->giveUserPermission($this->user, 'useraccessrestriction:deleteany');

        // Having deleteAny should not grant other permissions
        $this->assertTrue($this->policy->deleteAny($this->user));
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->create($this->user));
        $this->assertFalse($this->policy->updateAny($this->user));
        $this->assertFalse($this->policy->restoreAny($this->user));
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
