<?php

namespace Tests\Unit\Policies;

use App\Models\Asset;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\AssetPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetPolicyTest extends TestCase
{
    use RefreshDatabase;

    private AssetPolicy $policy;
    private User $user;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new AssetPolicy;
        $this->user = User::factory()->create();
        $this->asset = Asset::factory()->create();
    }

    public function test_view_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'asset:view');

        $this->assertTrue($this->policy->view($this->user, $this->asset));
    }

    public function test_view_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->view($this->user, $this->asset));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'asset:create');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    public function test_update_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'asset:update');

        $this->assertTrue($this->policy->update($this->user, $this->asset));
    }

    public function test_update_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->update($this->user, $this->asset));
    }

    public function test_delete_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'asset:delete');

        $this->assertTrue($this->policy->delete($this->user, $this->asset));
    }

    public function test_delete_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->delete($this->user, $this->asset));
    }

    public function test_add_access_grant_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'asset:addaccessgrant');

        $this->assertTrue($this->policy->addAccessGrant($this->user, $this->asset));
    }

    public function test_add_access_grant_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->addAccessGrant($this->user, $this->asset));
    }

    public function test_remove_access_grant_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'asset:removeaccessgrant');

        $this->assertTrue($this->policy->removeAccessGrant($this->user, $this->asset));
    }

    public function test_remove_access_grant_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->removeAccessGrant($this->user, $this->asset));
    }

    public function test_update_admin_account_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'asset:updateadminaccount');

        $this->assertTrue($this->policy->updateAdminAccount($this->user, $this->asset));
    }

    public function test_update_admin_account_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->updateAdminAccount($this->user, $this->asset));
    }

    public function test_view_requestable_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'asset:viewrequestable');

        $this->assertTrue($this->policy->viewRequestable($this->user));
    }

    public function test_view_requestable_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewRequestable($this->user));
    }

    public function test_user_with_multiple_permissions_can_perform_all_actions(): void
    {
        $this->giveUserPermission($this->user, 'asset:view');
        $this->giveUserPermission($this->user, 'asset:create');
        $this->giveUserPermission($this->user, 'asset:update');
        $this->giveUserPermission($this->user, 'asset:delete');
        $this->giveUserPermission($this->user, 'asset:addaccessgrant');
        $this->giveUserPermission($this->user, 'asset:removeaccessgrant');
        $this->giveUserPermission($this->user, 'asset:updateadminaccount');
        $this->giveUserPermission($this->user, 'asset:viewrequestable');

        $this->assertTrue($this->policy->view($this->user, $this->asset));
        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->update($this->user, $this->asset));
        $this->assertTrue($this->policy->delete($this->user, $this->asset));
        $this->assertTrue($this->policy->addAccessGrant($this->user, $this->asset));
        $this->assertTrue($this->policy->removeAccessGrant($this->user, $this->asset));
        $this->assertTrue($this->policy->updateAdminAccount($this->user, $this->asset));
        $this->assertTrue($this->policy->viewRequestable($this->user));
    }

    public function test_user_with_no_permissions_cannot_perform_any_actions(): void
    {
        $this->assertFalse($this->policy->view($this->user, $this->asset));
        $this->assertFalse($this->policy->create($this->user));
        $this->assertFalse($this->policy->update($this->user, $this->asset));
        $this->assertFalse($this->policy->delete($this->user, $this->asset));
        $this->assertFalse($this->policy->addAccessGrant($this->user, $this->asset));
        $this->assertFalse($this->policy->removeAccessGrant($this->user, $this->asset));
        $this->assertFalse($this->policy->updateAdminAccount($this->user, $this->asset));
        $this->assertFalse($this->policy->viewRequestable($this->user));
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
