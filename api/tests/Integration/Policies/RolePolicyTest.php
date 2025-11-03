<?php

namespace Tests\Unit\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\RolePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePolicyTest extends TestCase
{
    use RefreshDatabase;

    private RolePolicy $policy;
    private User $user;
    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new RolePolicy;
        $this->user = User::factory()->create();
        $this->role = Role::factory()->create();
    }

    public function test_view_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'role:view');

        $this->assertTrue($this->policy->view($this->user));
    }

    public function test_view_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->view($this->user));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'role:create');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    public function test_update_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'role:update');

        $this->assertTrue($this->policy->update($this->user));
    }

    public function test_update_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->update($this->user));
    }

    public function test_delete_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'role:delete');

        $this->assertTrue($this->policy->delete($this->user));
    }

    public function test_delete_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->delete($this->user));
    }

    public function test_list_permissions_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'role:listpermissions');

        $this->assertTrue($this->policy->listPermissions($this->user, $this->role));
    }

    public function test_list_permissions_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->listPermissions($this->user, $this->role));
    }

    public function test_update_permissions_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'role:updatepermissions');

        $this->assertTrue($this->policy->updatePermissions($this->user, $this->role));
    }

    public function test_update_permissions_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->updatePermissions($this->user, $this->role));
    }

    public function test_user_with_multiple_permissions_can_perform_all_actions(): void
    {
        $this->giveUserPermission($this->user, 'role:view');
        $this->giveUserPermission($this->user, 'role:create');
        $this->giveUserPermission($this->user, 'role:update');
        $this->giveUserPermission($this->user, 'role:delete');
        $this->giveUserPermission($this->user, 'role:listpermissions');
        $this->giveUserPermission($this->user, 'role:updatepermissions');

        $this->assertTrue($this->policy->view($this->user));
        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->update($this->user));
        $this->assertTrue($this->policy->delete($this->user));
        $this->assertTrue($this->policy->listPermissions($this->user, $this->role));
        $this->assertTrue($this->policy->updatePermissions($this->user, $this->role));
    }

    public function test_user_with_no_permissions_cannot_perform_any_actions(): void
    {
        $this->assertFalse($this->policy->view($this->user));
        $this->assertFalse($this->policy->create($this->user));
        $this->assertFalse($this->policy->update($this->user));
        $this->assertFalse($this->policy->delete($this->user));
        $this->assertFalse($this->policy->listPermissions($this->user, $this->role));
        $this->assertFalse($this->policy->updatePermissions($this->user, $this->role));
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
