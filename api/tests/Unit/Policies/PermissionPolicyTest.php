<?php

namespace Tests\Unit\Policies;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\PermissionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionPolicyTest extends TestCase
{
    use RefreshDatabase;

    private PermissionPolicy $policy;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new PermissionPolicy;
        $this->user = User::factory()->create();
    }

    public function test_view_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'permission:viewany');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'permission:create');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    public function test_update_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'permission:updateany');

        $this->assertTrue($this->policy->updateAny($this->user));
    }

    public function test_update_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->updateAny($this->user));
    }

    public function test_delete_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'permission:deleteany');

        $this->assertTrue($this->policy->deleteAny($this->user));
    }

    public function test_delete_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->deleteAny($this->user));
    }

    public function test_restore_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'permission:restoreany');

        $this->assertTrue($this->policy->restoreAny($this->user));
    }

    public function test_restore_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_user_with_multiple_permissions_can_perform_all_actions(): void
    {
        $this->giveUserPermission($this->user, 'permission:viewany');
        $this->giveUserPermission($this->user, 'permission:create');
        $this->giveUserPermission($this->user, 'permission:updateany');
        $this->giveUserPermission($this->user, 'permission:deleteany');
        $this->giveUserPermission($this->user, 'permission:restoreany');

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
