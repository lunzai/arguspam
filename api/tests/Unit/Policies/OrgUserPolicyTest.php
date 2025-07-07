<?php

namespace Tests\Unit\Policies;

use App\Models\Org;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\OrgUserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrgUserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private OrgUserPolicy $policy;
    private User $user;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new OrgUserPolicy;
        $this->user = User::factory()->create();
        $this->org = Org::factory()->create();
    }

    public function test_view_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'orguser:viewany');

        $this->assertTrue($this->policy->viewAny($this->user, $this->org));
    }

    public function test_view_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user, $this->org));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'orguser:create');

        $this->assertTrue($this->policy->create($this->user, $this->org));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user, $this->org));
    }

    public function test_delete_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'orguser:delete');

        $this->assertTrue($this->policy->delete($this->user, $this->org));
    }

    public function test_delete_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->delete($this->user, $this->org));
    }

    public function test_user_with_all_permissions_can_perform_all_actions(): void
    {
        $this->giveUserPermission($this->user, 'orguser:viewany');
        $this->giveUserPermission($this->user, 'orguser:create');
        $this->giveUserPermission($this->user, 'orguser:delete');

        $this->assertTrue($this->policy->viewAny($this->user, $this->org));
        $this->assertTrue($this->policy->create($this->user, $this->org));
        $this->assertTrue($this->policy->delete($this->user, $this->org));
    }

    public function test_user_with_no_permissions_cannot_perform_any_actions(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user, $this->org));
        $this->assertFalse($this->policy->create($this->user, $this->org));
        $this->assertFalse($this->policy->delete($this->user, $this->org));
    }

    public function test_user_with_only_viewany_permission(): void
    {
        $this->giveUserPermission($this->user, 'orguser:viewany');

        $this->assertTrue($this->policy->viewAny($this->user, $this->org));
        $this->assertFalse($this->policy->create($this->user, $this->org));
        $this->assertFalse($this->policy->delete($this->user, $this->org));
    }

    public function test_user_with_only_create_permission(): void
    {
        $this->giveUserPermission($this->user, 'orguser:create');

        $this->assertFalse($this->policy->viewAny($this->user, $this->org));
        $this->assertTrue($this->policy->create($this->user, $this->org));
        $this->assertFalse($this->policy->delete($this->user, $this->org));
    }

    public function test_user_with_only_delete_permission(): void
    {
        $this->giveUserPermission($this->user, 'orguser:delete');

        $this->assertFalse($this->policy->viewAny($this->user, $this->org));
        $this->assertFalse($this->policy->create($this->user, $this->org));
        $this->assertTrue($this->policy->delete($this->user, $this->org));
    }

    public function test_multiple_users_with_different_permissions(): void
    {
        $viewAnyUser = User::factory()->create();
        $createUser = User::factory()->create();
        $deleteUser = User::factory()->create();
        $allPermissionsUser = User::factory()->create();

        $this->giveUserPermission($viewAnyUser, 'orguser:viewany');
        $this->giveUserPermission($createUser, 'orguser:create');
        $this->giveUserPermission($deleteUser, 'orguser:delete');
        $this->giveUserPermission($allPermissionsUser, 'orguser:viewany');
        $this->giveUserPermission($allPermissionsUser, 'orguser:create');
        $this->giveUserPermission($allPermissionsUser, 'orguser:delete');

        // ViewAny user
        $this->assertTrue($this->policy->viewAny($viewAnyUser, $this->org));
        $this->assertFalse($this->policy->create($viewAnyUser, $this->org));
        $this->assertFalse($this->policy->delete($viewAnyUser, $this->org));

        // Create user
        $this->assertFalse($this->policy->viewAny($createUser, $this->org));
        $this->assertTrue($this->policy->create($createUser, $this->org));
        $this->assertFalse($this->policy->delete($createUser, $this->org));

        // Delete user
        $this->assertFalse($this->policy->viewAny($deleteUser, $this->org));
        $this->assertFalse($this->policy->create($deleteUser, $this->org));
        $this->assertTrue($this->policy->delete($deleteUser, $this->org));

        // All permissions user
        $this->assertTrue($this->policy->viewAny($allPermissionsUser, $this->org));
        $this->assertTrue($this->policy->create($allPermissionsUser, $this->org));
        $this->assertTrue($this->policy->delete($allPermissionsUser, $this->org));

        // No permissions user
        $this->assertFalse($this->policy->viewAny($this->user, $this->org));
        $this->assertFalse($this->policy->create($this->user, $this->org));
        $this->assertFalse($this->policy->delete($this->user, $this->org));
    }

    public function test_org_parameter_is_accepted_but_not_used_for_authorization(): void
    {
        // The current implementation doesn't use the $org parameter for authorization
        // It only checks if user has the permission regardless of the org
        $org1 = Org::factory()->create(['name' => 'Org 1']);
        $org2 = Org::factory()->create(['name' => 'Org 2']);

        $this->giveUserPermission($this->user, 'orguser:viewany');

        // Should return same result for any org
        $this->assertTrue($this->policy->viewAny($this->user, $org1));
        $this->assertTrue($this->policy->viewAny($this->user, $org2));
        $this->assertTrue($this->policy->viewAny($this->user, $this->org));
    }

    public function test_permission_names_are_case_insensitive(): void
    {
        $this->giveUserPermission($this->user, 'ORGUSER:VIEWANY');

        $this->assertTrue($this->policy->viewAny($this->user, $this->org));
    }

    public function test_permissions_are_independent(): void
    {
        // Each permission should only grant access to its specific action
        $this->giveUserPermission($this->user, 'orguser:create');

        $this->assertTrue($this->policy->create($this->user, $this->org));
        $this->assertFalse($this->policy->viewAny($this->user, $this->org));
        $this->assertFalse($this->policy->delete($this->user, $this->org));
    }

    public function test_create_and_delete_are_independent_operations(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        // Give different permissions to different users
        $this->giveUserPermission($user1, 'orguser:create');
        $this->giveUserPermission($user2, 'orguser:delete');

        // Each user can only perform the action they have permission for
        $this->assertTrue($this->policy->create($user1, $this->org));
        $this->assertFalse($this->policy->delete($user1, $this->org));

        $this->assertFalse($this->policy->create($user2, $this->org));
        $this->assertTrue($this->policy->delete($user2, $this->org));
    }

    public function test_policy_handles_permission_edge_cases(): void
    {
        // Test with similar but different permission names
        $this->giveUserPermission($this->user, 'orguser:created'); // Note: 'created' not 'create'

        $this->assertFalse($this->policy->create($this->user, $this->org)); // Should not match
        $this->assertFalse($this->policy->viewAny($this->user, $this->org));
        $this->assertFalse($this->policy->delete($this->user, $this->org));
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
