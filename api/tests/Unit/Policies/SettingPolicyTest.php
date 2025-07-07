<?php

namespace Tests\Unit\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Policies\SettingPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingPolicyTest extends TestCase
{
    use RefreshDatabase;

    private SettingPolicy $policy;
    private User $user;
    private Setting $setting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new SettingPolicy;
        $this->user = User::factory()->create();
        $this->setting = Setting::factory()->create();
    }

    public function test_view_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'setting:viewany');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_view_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'setting:view');

        $this->assertTrue($this->policy->view($this->user, $this->setting));
    }

    public function test_view_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->view($this->user, $this->setting));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'setting:create');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    public function test_update_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'setting:update');

        $this->assertTrue($this->policy->update($this->user, $this->setting));
    }

    public function test_update_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->update($this->user, $this->setting));
    }

    public function test_delete_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'setting:delete');

        $this->assertTrue($this->policy->delete($this->user, $this->setting));
    }

    public function test_delete_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->delete($this->user, $this->setting));
    }

    public function test_restore_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'setting:restore');

        $this->assertTrue($this->policy->restore($this->user, $this->setting));
    }

    public function test_restore_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->restore($this->user, $this->setting));
    }

    public function test_force_delete_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'setting:forcedelete');

        $this->assertTrue($this->policy->forceDelete($this->user, $this->setting));
    }

    public function test_force_delete_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->forceDelete($this->user, $this->setting));
    }

    public function test_user_with_all_permissions_can_perform_all_actions(): void
    {
        $permissions = [
            'setting:viewany', 'setting:view', 'setting:create', 'setting:update',
            'setting:delete', 'setting:restore', 'setting:forcedelete',
        ];

        foreach ($permissions as $permission) {
            $this->giveUserPermission($this->user, $permission);
        }

        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->view($this->user, $this->setting));
        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->update($this->user, $this->setting));
        $this->assertTrue($this->policy->delete($this->user, $this->setting));
        $this->assertTrue($this->policy->restore($this->user, $this->setting));
        $this->assertTrue($this->policy->forceDelete($this->user, $this->setting));
    }

    public function test_user_with_no_permissions_cannot_perform_any_actions(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->view($this->user, $this->setting));
        $this->assertFalse($this->policy->create($this->user));
        $this->assertFalse($this->policy->update($this->user, $this->setting));
        $this->assertFalse($this->policy->delete($this->user, $this->setting));
        $this->assertFalse($this->policy->restore($this->user, $this->setting));
        $this->assertFalse($this->policy->forceDelete($this->user, $this->setting));
    }

    public function test_multiple_users_with_different_permissions(): void
    {
        $viewAnyUser = User::factory()->create();
        $viewUser = User::factory()->create();
        $createUser = User::factory()->create();
        $updateUser = User::factory()->create();
        $deleteUser = User::factory()->create();
        $restoreUser = User::factory()->create();
        $forceDeleteUser = User::factory()->create();

        $this->giveUserPermission($viewAnyUser, 'setting:viewany');
        $this->giveUserPermission($viewUser, 'setting:view');
        $this->giveUserPermission($createUser, 'setting:create');
        $this->giveUserPermission($updateUser, 'setting:update');
        $this->giveUserPermission($deleteUser, 'setting:delete');
        $this->giveUserPermission($restoreUser, 'setting:restore');
        $this->giveUserPermission($forceDeleteUser, 'setting:forcedelete');

        // ViewAny user
        $this->assertTrue($this->policy->viewAny($viewAnyUser));
        $this->assertFalse($this->policy->view($viewAnyUser, $this->setting));
        $this->assertFalse($this->policy->create($viewAnyUser));

        // View user
        $this->assertFalse($this->policy->viewAny($viewUser));
        $this->assertTrue($this->policy->view($viewUser, $this->setting));
        $this->assertFalse($this->policy->create($viewUser));

        // Create user
        $this->assertFalse($this->policy->viewAny($createUser));
        $this->assertFalse($this->policy->view($createUser, $this->setting));
        $this->assertTrue($this->policy->create($createUser));

        // Update user
        $this->assertFalse($this->policy->create($updateUser));
        $this->assertTrue($this->policy->update($updateUser, $this->setting));
        $this->assertFalse($this->policy->delete($updateUser, $this->setting));

        // Delete user
        $this->assertFalse($this->policy->update($deleteUser, $this->setting));
        $this->assertTrue($this->policy->delete($deleteUser, $this->setting));
        $this->assertFalse($this->policy->restore($deleteUser, $this->setting));

        // Restore user
        $this->assertFalse($this->policy->delete($restoreUser, $this->setting));
        $this->assertTrue($this->policy->restore($restoreUser, $this->setting));
        $this->assertFalse($this->policy->forceDelete($restoreUser, $this->setting));

        // Force delete user
        $this->assertFalse($this->policy->restore($forceDeleteUser, $this->setting));
        $this->assertTrue($this->policy->forceDelete($forceDeleteUser, $this->setting));
    }

    public function test_permission_names_are_case_insensitive(): void
    {
        $this->giveUserPermission($this->user, 'SETTING:VIEWANY');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_permissions_are_independent(): void
    {
        // Each permission should only grant access to its specific action
        $this->giveUserPermission($this->user, 'setting:create');

        $this->assertTrue($this->policy->create($this->user));
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->view($this->user, $this->setting));
        $this->assertFalse($this->policy->update($this->user, $this->setting));
        $this->assertFalse($this->policy->delete($this->user, $this->setting));
        $this->assertFalse($this->policy->restore($this->user, $this->setting));
        $this->assertFalse($this->policy->forceDelete($this->user, $this->setting));
    }

    public function test_setting_parameter_is_accepted_but_not_used_for_authorization(): void
    {
        // The current implementation doesn't use the $setting parameter for authorization
        // It only checks if user has the permission regardless of the setting
        $setting1 = Setting::factory()->create(['key' => 'setting1']);
        $setting2 = Setting::factory()->create(['key' => 'setting2']);

        $this->giveUserPermission($this->user, 'setting:view');

        // Should return same result for any setting
        $this->assertTrue($this->policy->view($this->user, $setting1));
        $this->assertTrue($this->policy->view($this->user, $setting2));
        $this->assertTrue($this->policy->view($this->user, $this->setting));
    }

    public function test_policy_handles_permission_edge_cases(): void
    {
        // Test with similar but different permission names
        $this->giveUserPermission($this->user, 'setting:created'); // Note: 'created' not 'create'

        $this->assertFalse($this->policy->create($this->user)); // Should not match
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->view($this->user, $this->setting));
    }

    public function test_crud_operations_are_independent(): void
    {
        // Test that CRUD permissions are completely independent
        $createOnlyUser = User::factory()->create();
        $updateOnlyUser = User::factory()->create();
        $deleteOnlyUser = User::factory()->create();

        $this->giveUserPermission($createOnlyUser, 'setting:create');
        $this->giveUserPermission($updateOnlyUser, 'setting:update');
        $this->giveUserPermission($deleteOnlyUser, 'setting:delete');

        // Create-only user
        $this->assertTrue($this->policy->create($createOnlyUser));
        $this->assertFalse($this->policy->update($createOnlyUser, $this->setting));
        $this->assertFalse($this->policy->delete($createOnlyUser, $this->setting));

        // Update-only user
        $this->assertFalse($this->policy->create($updateOnlyUser));
        $this->assertTrue($this->policy->update($updateOnlyUser, $this->setting));
        $this->assertFalse($this->policy->delete($updateOnlyUser, $this->setting));

        // Delete-only user
        $this->assertFalse($this->policy->create($deleteOnlyUser));
        $this->assertFalse($this->policy->update($deleteOnlyUser, $this->setting));
        $this->assertTrue($this->policy->delete($deleteOnlyUser, $this->setting));
    }

    public function test_policy_with_different_permission_variations(): void
    {
        // Test that only exact permission match works
        $variations = [
            'setting:views',
            'setting:viewing',
            'setting:creates',
            'setting:updates',
            'setting:deletes',
            'settings:view',
            'config:view',
        ];

        foreach ($variations as $variation) {
            $testUser = User::factory()->create();
            $this->giveUserPermission($testUser, $variation);

            $this->assertFalse($this->policy->view($testUser, $this->setting), "Permission '{$variation}' should not grant view access");
            $this->assertFalse($this->policy->create($testUser), "Permission '{$variation}' should not grant create access");
            $this->assertFalse($this->policy->update($testUser, $this->setting), "Permission '{$variation}' should not grant update access");
        }
    }

    public function test_user_with_multiple_roles_and_permissions(): void
    {
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();

        $permission1 = Permission::firstOrCreate(
            ['name' => 'setting:view'],
            ['description' => 'View Setting']
        );
        $permission2 = Permission::firstOrCreate(
            ['name' => 'setting:update'],
            ['description' => 'Update Setting']
        );

        $role1->permissions()->attach($permission1);
        $role2->permissions()->attach($permission2);

        $this->user->roles()->attach([$role1->id, $role2->id]);
        $this->user->clearUserRolePermissionCache();

        $this->assertTrue($this->policy->view($this->user, $this->setting));
        $this->assertTrue($this->policy->update($this->user, $this->setting));
        $this->assertFalse($this->policy->delete($this->user, $this->setting));
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
