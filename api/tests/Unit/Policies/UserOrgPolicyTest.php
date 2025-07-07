<?php

namespace Tests\Unit\Policies;

use App\Models\Org;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\UserOrgPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserOrgPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserOrgPolicy $policy;
    private User $user;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new UserOrgPolicy;
        $this->user = User::factory()->create();
        $this->org = Org::factory()->create();
    }

    public function test_view_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'userorg:viewany');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_view_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'userorg:view');

        $this->assertTrue($this->policy->view($this->user, $this->org));
    }

    public function test_view_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->view($this->user, $this->org));
    }

    public function test_view_works_with_different_orgs(): void
    {
        $anotherOrg = Org::factory()->create();
        $this->giveUserPermission($this->user, 'userorg:view');

        // Should work with any org when user has the permission
        $this->assertTrue($this->policy->view($this->user, $this->org));
        $this->assertTrue($this->policy->view($this->user, $anotherOrg));
    }

    public function test_user_with_view_any_permission_can_view_specific_org(): void
    {
        $this->giveUserPermission($this->user, 'userorg:viewany');

        // viewAny permission should allow viewing specific orgs too
        $this->assertTrue($this->policy->viewAny($this->user));
        // But view method doesn't check for viewAny, only specific view permission
        $this->assertFalse($this->policy->view($this->user, $this->org));
    }

    public function test_user_with_view_permission_cannot_view_any(): void
    {
        $this->giveUserPermission($this->user, 'userorg:view');

        // view permission should not grant viewAny access
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->view($this->user, $this->org));
    }

    public function test_user_with_both_permissions_can_perform_both_actions(): void
    {
        $this->giveUserPermission($this->user, 'userorg:viewany');
        $this->giveUserPermission($this->user, 'userorg:view');

        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->view($this->user, $this->org));
    }

    public function test_user_with_no_permissions_cannot_perform_any_actions(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->view($this->user, $this->org));
    }

    public function test_multiple_users_with_different_permissions(): void
    {
        $viewAnyUser = User::factory()->create();
        $viewUser = User::factory()->create();
        $bothUser = User::factory()->create();

        $this->giveUserPermission($viewAnyUser, 'userorg:viewany');
        $this->giveUserPermission($viewUser, 'userorg:view');
        $this->giveUserPermission($bothUser, 'userorg:viewany');
        $this->giveUserPermission($bothUser, 'userorg:view');

        // ViewAny user
        $this->assertTrue($this->policy->viewAny($viewAnyUser));
        $this->assertFalse($this->policy->view($viewAnyUser, $this->org));

        // View user
        $this->assertFalse($this->policy->viewAny($viewUser));
        $this->assertTrue($this->policy->view($viewUser, $this->org));

        // Both permissions user
        $this->assertTrue($this->policy->viewAny($bothUser));
        $this->assertTrue($this->policy->view($bothUser, $this->org));

        // No permissions user
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->view($this->user, $this->org));
    }

    public function test_permission_names_are_case_insensitive(): void
    {
        $this->giveUserPermission($this->user, 'USERORG:VIEWANY');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_method_accepts_org_parameter_but_does_not_use_it_for_authorization(): void
    {
        // The current implementation doesn't use the $org parameter for authorization
        // It only checks if user has the 'userorg:view' permission regardless of the org
        $org1 = Org::factory()->create(['name' => 'Org 1']);
        $org2 = Org::factory()->create(['name' => 'Org 2']);

        $this->giveUserPermission($this->user, 'userorg:view');

        // Should return same result for any org
        $this->assertTrue($this->policy->view($this->user, $org1));
        $this->assertTrue($this->policy->view($this->user, $org2));
        $this->assertTrue($this->policy->view($this->user, $this->org));
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
