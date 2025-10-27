<?php

namespace Tests\Unit\Policies;

use App\Enums\AssetAccessRole;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\Permission;
use App\Models\Request;
use App\Models\Role;
use App\Models\User;
use App\Policies\RequestPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequestPolicyTest extends TestCase
{
    use RefreshDatabase;

    private RequestPolicy $policy;
    private User $user;
    private User $requester;
    private User $approver;
    private Request $request;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new RequestPolicy;
        $this->user = User::factory()->create();
        $this->requester = User::factory()->create();
        $this->approver = User::factory()->create();
        $this->asset = Asset::factory()->create();
        $this->request = Request::factory()->create([
            'requester_id' => $this->requester->id,
            'asset_id' => $this->asset->id,
        ]);
    }

    public function test_view_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:viewany');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_view_returns_true_when_user_has_view_any_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:viewany');

        $this->assertTrue($this->policy->view($this->user, $this->request));
    }

    public function test_view_returns_true_when_user_is_requester_with_view_permission(): void
    {
        $this->giveUserPermission($this->requester, 'request:view');

        $this->assertTrue($this->policy->view($this->requester, $this->request));
    }

    public function test_view_returns_false_when_user_is_requester_without_view_permission(): void
    {
        $this->assertFalse($this->policy->view($this->requester, $this->request));
    }

    public function test_view_returns_false_when_user_is_not_requester_and_lacks_view_any_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:view');

        $this->assertFalse($this->policy->view($this->user, $this->request));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:create');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    public function test_update_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:updateany');

        $this->assertTrue($this->policy->updateAny($this->user));
    }

    public function test_update_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->updateAny($this->user));
    }

    public function test_update_returns_true_when_user_has_update_any_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:updateany');

        $this->assertTrue($this->policy->update($this->user, $this->request));
    }

    public function test_update_returns_true_when_user_is_requester_with_update_permission(): void
    {
        $this->giveUserPermission($this->requester, 'request:update');

        $this->assertTrue($this->policy->update($this->requester, $this->request));
    }

    public function test_update_returns_false_when_user_is_requester_without_update_permission(): void
    {
        $this->assertFalse($this->policy->update($this->requester, $this->request));
    }

    public function test_update_returns_false_when_user_is_not_requester_and_lacks_update_any_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:update');

        $this->assertFalse($this->policy->update($this->user, $this->request));
    }

    public function test_approve_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:approveany');

        $this->assertTrue($this->policy->approveAny($this->user));
    }

    public function test_approve_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->approveAny($this->user));
    }

    public function test_approve_returns_true_when_user_has_approve_any_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:approveany');
        $this->request->submit();

        $this->assertTrue($this->policy->approve($this->user, $this->request));
    }

    public function test_approve_returns_true_when_user_can_approve_asset_and_has_permission_and_is_not_requester(): void
    {
        $this->giveUserPermission($this->approver, 'request:approve');
        $this->giveUserAssetApprovalAccess($this->approver, $this->asset);
        $this->request->submit();

        $this->assertTrue($this->policy->approve($this->approver, $this->request));
    }

    public function test_approve_returns_false_when_user_is_requester(): void
    {
        $this->giveUserPermission($this->requester, 'request:approve');
        $this->giveUserAssetApprovalAccess($this->requester, $this->asset);

        $this->assertFalse($this->policy->approve($this->requester, $this->request));
    }

    public function test_approve_returns_false_when_user_cannot_approve_asset(): void
    {
        $this->giveUserPermission($this->approver, 'request:approve');

        $this->assertFalse($this->policy->approve($this->approver, $this->request));
    }

    public function test_approve_returns_false_when_user_lacks_approve_permission(): void
    {
        $this->giveUserAssetApprovalAccess($this->approver, $this->asset);

        $this->assertFalse($this->policy->approve($this->approver, $this->request));
    }

    public function test_reject_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:rejectany');

        $this->assertTrue($this->policy->rejectAny($this->user));
    }

    public function test_reject_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->rejectAny($this->user));
    }

    public function test_reject_returns_true_when_user_has_reject_any_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:rejectany');
        $this->request->submit();

        $this->assertTrue($this->policy->reject($this->user, $this->request));
    }

    public function test_reject_returns_true_when_user_can_approve_asset_and_has_permission_and_is_not_requester(): void
    {
        $this->giveUserPermission($this->approver, 'request:reject');
        $this->giveUserAssetApprovalAccess($this->approver, $this->asset);
        $this->request->submit();

        $this->assertTrue($this->policy->reject($this->approver, $this->request));
    }

    public function test_reject_returns_false_when_user_is_requester(): void
    {
        $this->giveUserPermission($this->requester, 'request:reject');
        $this->giveUserAssetApprovalAccess($this->requester, $this->asset);

        $this->assertFalse($this->policy->reject($this->requester, $this->request));
    }

    public function test_reject_returns_false_when_user_cannot_approve_asset(): void
    {
        $this->giveUserPermission($this->approver, 'request:reject');

        $this->assertFalse($this->policy->reject($this->approver, $this->request));
    }

    public function test_reject_returns_false_when_user_lacks_reject_permission(): void
    {
        $this->giveUserAssetApprovalAccess($this->approver, $this->asset);

        $this->assertFalse($this->policy->reject($this->approver, $this->request));
    }

    public function test_delete_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:deleteany');

        $this->assertTrue($this->policy->deleteAny($this->user));
    }

    public function test_delete_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->deleteAny($this->user));
    }

    public function test_restore_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'request:restoreany');

        $this->assertTrue($this->policy->restoreAny($this->user));
    }

    public function test_restore_any_returns_false_when_user_lacks_permission(): void
    {
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

    private function giveUserAssetApprovalAccess(User $user, Asset $asset): void
    {
        AssetAccessGrant::factory()->create([
            'user_id' => $user->id,
            'asset_id' => $asset->id,
            'role' => AssetAccessRole::APPROVER,
        ]);
    }
}
