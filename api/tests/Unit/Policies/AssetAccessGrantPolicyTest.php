<?php

namespace Tests\Unit\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\AssetAccessGrantPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetAccessGrantPolicyTest extends TestCase
{
    use RefreshDatabase;

    private AssetAccessGrantPolicy $policy;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new AssetAccessGrantPolicy;
        $this->user = User::factory()->create();
    }

    public function test_view_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'assetaccessgrant:viewany');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_view_returns_true_when_user_has_viewany_permission(): void
    {
        $this->giveUserPermission($this->user, 'assetaccessgrant:viewany');

        $this->assertTrue($this->policy->view($this->user));
    }

    public function test_view_returns_true_when_user_has_view_permission(): void
    {
        $this->giveUserPermission($this->user, 'assetaccessgrant:view');

        $this->assertTrue($this->policy->view($this->user));
    }

    public function test_view_returns_true_when_user_has_both_permissions(): void
    {
        $this->giveUserPermission($this->user, 'assetaccessgrant:viewany');
        $this->giveUserPermission($this->user, 'assetaccessgrant:view');

        $this->assertTrue($this->policy->view($this->user));
    }

    public function test_view_returns_false_when_user_lacks_both_permissions(): void
    {
        $this->assertFalse($this->policy->view($this->user));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'assetaccessgrant:create');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    public function test_update_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'assetaccessgrant:updateany');

        $this->assertTrue($this->policy->updateAny($this->user));
    }

    public function test_update_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->updateAny($this->user));
    }

    public function test_delete_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'assetaccessgrant:deleteany');

        $this->assertTrue($this->policy->deleteAny($this->user));
    }

    public function test_delete_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->deleteAny($this->user));
    }

    public function test_restore_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'assetaccessgrant:restoreany');

        $this->assertTrue($this->policy->restoreAny($this->user));
    }

    public function test_restore_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_user_with_all_permissions_can_perform_all_actions(): void
    {
        $this->giveUserPermission($this->user, 'assetaccessgrant:viewany');
        $this->giveUserPermission($this->user, 'assetaccessgrant:view');
        $this->giveUserPermission($this->user, 'assetaccessgrant:create');
        $this->giveUserPermission($this->user, 'assetaccessgrant:updateany');
        $this->giveUserPermission($this->user, 'assetaccessgrant:deleteany');
        $this->giveUserPermission($this->user, 'assetaccessgrant:restoreany');

        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->view($this->user));
        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->updateAny($this->user));
        $this->assertTrue($this->policy->deleteAny($this->user));
        $this->assertTrue($this->policy->restoreAny($this->user));
    }

    public function test_user_with_no_permissions_cannot_perform_any_actions(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->view($this->user));
        $this->assertFalse($this->policy->create($this->user));
        $this->assertFalse($this->policy->updateAny($this->user));
        $this->assertFalse($this->policy->deleteAny($this->user));
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_view_method_uses_or_logic_correctly(): void
    {
        // Test that view() returns true if user has viewany permission (even without view permission)
        $viewAnyUser = User::factory()->create();
        $this->giveUserPermission($viewAnyUser, 'assetaccessgrant:viewany');
        $this->assertTrue($this->policy->view($viewAnyUser));

        // Test that view() returns true if user has view permission (even without viewany permission)
        $viewUser = User::factory()->create();
        $this->giveUserPermission($viewUser, 'assetaccessgrant:view');
        $this->assertTrue($this->policy->view($viewUser));
    }

    public function test_multiple_users_with_different_permissions(): void
    {
        $viewAnyUser = User::factory()->create();
        $viewUser = User::factory()->create();
        $createUser = User::factory()->create();
        $updateUser = User::factory()->create();
        $deleteUser = User::factory()->create();
        $restoreUser = User::factory()->create();

        $this->giveUserPermission($viewAnyUser, 'assetaccessgrant:viewany');
        $this->giveUserPermission($viewUser, 'assetaccessgrant:view');
        $this->giveUserPermission($createUser, 'assetaccessgrant:create');
        $this->giveUserPermission($updateUser, 'assetaccessgrant:updateany');
        $this->giveUserPermission($deleteUser, 'assetaccessgrant:deleteany');
        $this->giveUserPermission($restoreUser, 'assetaccessgrant:restoreany');

        // ViewAny user
        $this->assertTrue($this->policy->viewAny($viewAnyUser));
        $this->assertTrue($this->policy->view($viewAnyUser)); // Should also work for view
        $this->assertFalse($this->policy->create($viewAnyUser));

        // View user
        $this->assertFalse($this->policy->viewAny($viewUser));
        $this->assertTrue($this->policy->view($viewUser));
        $this->assertFalse($this->policy->create($viewUser));

        // Create user
        $this->assertFalse($this->policy->viewAny($createUser));
        $this->assertFalse($this->policy->view($createUser));
        $this->assertTrue($this->policy->create($createUser));

        // Update user
        $this->assertFalse($this->policy->viewAny($updateUser));
        $this->assertTrue($this->policy->updateAny($updateUser));
        $this->assertFalse($this->policy->deleteAny($updateUser));

        // Delete user
        $this->assertFalse($this->policy->updateAny($deleteUser));
        $this->assertTrue($this->policy->deleteAny($deleteUser));
        $this->assertFalse($this->policy->restoreAny($deleteUser));

        // Restore user
        $this->assertFalse($this->policy->deleteAny($restoreUser));
        $this->assertTrue($this->policy->restoreAny($restoreUser));
    }

    public function test_permission_names_are_case_insensitive(): void
    {
        $this->giveUserPermission($this->user, 'ASSETACCESSGRANT:VIEWANY');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_permissions_are_independent(): void
    {
        // Each permission should only grant access to its specific action
        $this->giveUserPermission($this->user, 'assetaccessgrant:create');

        $this->assertTrue($this->policy->create($this->user));
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->view($this->user));
        $this->assertFalse($this->policy->updateAny($this->user));
        $this->assertFalse($this->policy->deleteAny($this->user));
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
