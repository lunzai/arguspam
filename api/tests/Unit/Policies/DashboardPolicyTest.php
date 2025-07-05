<?php

namespace Tests\Unit\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\DashboardPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardPolicyTest extends TestCase
{
    use RefreshDatabase;

    private DashboardPolicy $policy;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new DashboardPolicy();
        $this->user = User::factory()->create();
    }

    public function test_viewAny_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'dashboard:viewany');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_viewAny_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_user_with_no_permissions_cannot_view_any(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_multiple_users_with_different_permissions(): void
    {
        $authorizedUser = User::factory()->create();
        $unauthorizedUser = User::factory()->create();

        $this->giveUserPermission($authorizedUser, 'dashboard:viewany');

        $this->assertTrue($this->policy->viewAny($authorizedUser));
        $this->assertFalse($this->policy->viewAny($unauthorizedUser));
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_permission_names_are_case_insensitive(): void
    {
        $this->giveUserPermission($this->user, 'DASHBOARD:VIEWANY');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_viewAny_with_similar_but_different_permission_names(): void
    {
        // Test with similar but different permission names
        $this->giveUserPermission($this->user, 'dashboard:view'); // Note: 'view' not 'viewany'

        $this->assertFalse($this->policy->viewAny($this->user)); // Should not match
    }

    public function test_policy_handles_edge_cases(): void
    {
        // Test with permission that starts with the same prefix but is different
        $this->giveUserPermission($this->user, 'dashboard:viewanyother');

        $this->assertFalse($this->policy->viewAny($this->user)); // Should not match
    }

    public function test_user_with_multiple_roles_and_permissions(): void
    {
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();
        
        $permission1 = Permission::firstOrCreate(
            ['name' => 'dashboard:viewany'],
            ['description' => 'View Any Dashboard']
        );
        $permission2 = Permission::firstOrCreate(
            ['name' => 'some:other'],
            ['description' => 'Some Other Permission']
        );

        $role1->permissions()->attach($permission1);
        $role2->permissions()->attach($permission2);
        
        $this->user->roles()->attach([$role1->id, $role2->id]);
        $this->user->clearUserRolePermissionCache();

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_dashboard_permission_is_independent_of_other_permissions(): void
    {
        // User with other permissions but not dashboard should not have access
        $userWithOtherPerms = User::factory()->create();
        $this->giveUserPermission($userWithOtherPerms, 'user:view');
        $this->giveUserPermission($userWithOtherPerms, 'asset:view');
        
        $this->assertFalse($this->policy->viewAny($userWithOtherPerms));
        
        // Different user with dashboard permission should have access
        $userWithDashboard = User::factory()->create();
        $this->giveUserPermission($userWithDashboard, 'dashboard:viewany');
        $this->assertTrue($this->policy->viewAny($userWithDashboard));
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