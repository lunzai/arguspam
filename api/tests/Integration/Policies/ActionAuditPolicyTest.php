<?php

namespace Tests\Integration\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\ActionAuditPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActionAuditPolicyTest extends TestCase
{
    use RefreshDatabase;

    private ActionAuditPolicy $policy;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new ActionAuditPolicy;
        $this->user = User::factory()->create();
    }

    public function test_view_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'actionaudit:view');

        $this->assertTrue($this->policy->view($this->user));
    }

    public function test_view_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->view($this->user));
    }

    public function test_user_with_no_permissions_cannot_view(): void
    {
        $this->assertFalse($this->policy->view($this->user));
    }

    public function test_multiple_users_with_different_permissions(): void
    {
        $authorizedUser = User::factory()->create();
        $unauthorizedUser = User::factory()->create();

        $this->giveUserPermission($authorizedUser, 'actionaudit:view');

        $this->assertTrue($this->policy->view($authorizedUser));
        $this->assertFalse($this->policy->view($unauthorizedUser));
        $this->assertFalse($this->policy->view($this->user));
    }

    public function test_permission_names_are_case_insensitive(): void
    {
        $this->giveUserPermission($this->user, 'ACTIONAUDIT:VIEW');

        $this->assertTrue($this->policy->view($this->user));
    }

    public function test_view_with_similar_but_different_permission_names(): void
    {
        // Test with similar but different permission names
        $this->giveUserPermission($this->user, 'actionaudit:viewall'); // Note: 'viewall' not 'view'

        $this->assertFalse($this->policy->view($this->user)); // Should not match
    }

    public function test_policy_handles_edge_cases(): void
    {
        // Test with permission that starts with the same prefix but is different
        $this->giveUserPermission($this->user, 'actionaudit:viewother');

        $this->assertFalse($this->policy->view($this->user)); // Should not match
    }

    public function test_user_with_multiple_roles_and_permissions(): void
    {
        $role1 = Role::factory()->create();
        $role2 = Role::factory()->create();

        $permission1 = Permission::firstOrCreate(
            ['name' => 'actionaudit:view'],
            ['description' => 'View Action Audit']
        );
        $permission2 = Permission::firstOrCreate(
            ['name' => 'some:other'],
            ['description' => 'Some Other Permission']
        );

        $role1->permissions()->attach($permission1);
        $role2->permissions()->attach($permission2);

        $this->user->roles()->attach([$role1->id, $role2->id]);
        $this->user->clearUserRolePermissionCache();

        $this->assertTrue($this->policy->view($this->user));
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
