<?php

namespace Tests\Unit\Policies;

use App\Enums\AssetAccessRole;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\Org;
use App\Models\Permission;
use App\Models\Request as RequestModel;
use App\Models\Role;
use App\Models\Session;
use App\Models\SessionAudit;
use App\Models\User;
use App\Models\UserGroup;
use App\Policies\SessionAuditPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionAuditPolicyTest extends TestCase
{
    use RefreshDatabase;

    private SessionAuditPolicy $policy;
    private User $user;
    private User $approver;
    private Org $org;
    private Asset $asset;
    private RequestModel $request;
    private Session $session;
    private SessionAudit $sessionAudit;

    // protected function setUp(): void
    // {
    //     parent::setUp();

    //     $this->policy = new SessionAuditPolicy();
    //     $this->user = User::factory()->create();
    //     $this->approver = User::factory()->create();
    //     $this->org = Org::factory()->create();
    //     $this->asset = Asset::factory()->create();
    //     $this->request = RequestModel::factory()->create();
    //     $this->session = Session::factory()->create([
    //         'org_id' => $this->org->id,
    //         'asset_id' => $this->asset->id,
    //         'request_id' => $this->request->id,
    //         'requester_id' => $this->user->id,
    //         'approver_id' => $this->approver->id,
    //         'start_datetime' => now(),
    //         'end_datetime' => now()->addHour(),
    //         'scheduled_end_datetime' => now()->addHour(),
    //         'requested_duration' => 60,
    //         'actual_duration' => 60,
    //     ]);
    //     $this->sessionAudit = SessionAudit::factory()->create([
    //         'org_id' => $this->org->id,
    //         'session_id' => $this->session->id,
    //         'request_id' => $this->request->id,
    //         'asset_id' => $this->asset->id,
    //     ]);
    // }

    // public function test_viewAny_returns_true_when_user_has_permission(): void
    // {
    //     $this->giveUserPermission($this->user, 'sessionaudit:viewany');

    //     $this->assertTrue($this->policy->viewAny($this->user));
    // }

    // public function test_viewAny_returns_false_when_user_lacks_permission(): void
    // {
    //     $this->assertFalse($this->policy->viewAny($this->user));
    // }

    // public function test_view_returns_true_when_user_has_viewany_permission(): void
    // {
    //     $this->giveUserPermission($this->user, 'sessionaudit:viewany');

    //     $this->assertTrue($this->policy->view($this->user, $this->sessionAudit));
    // }

    // public function test_view_returns_true_when_user_has_asset_access_and_view_permission(): void
    // {
    //     $this->giveUserAssetAccess($this->user, $this->asset);
    //     $this->giveUserPermission($this->user, 'sessionaudit:view');

    //     $this->assertTrue($this->policy->view($this->user, $this->sessionAudit));
    // }

    // public function test_view_returns_false_when_user_has_view_permission_but_no_asset_access(): void
    // {
    //     $this->giveUserPermission($this->user, 'sessionaudit:view');

    //     $this->assertFalse($this->policy->view($this->user, $this->sessionAudit));
    // }

    // public function test_view_returns_false_when_user_has_asset_access_but_no_view_permission(): void
    // {
    //     $this->giveUserAssetAccess($this->user, $this->asset);

    //     $this->assertFalse($this->policy->view($this->user, $this->sessionAudit));
    // }

    // public function test_view_returns_false_when_user_lacks_both_conditions(): void
    // {
    //     $this->assertFalse($this->policy->view($this->user, $this->sessionAudit));
    // }

    // public function test_view_with_group_based_asset_access(): void
    // {
    //     $userGroup = UserGroup::factory()->create();
    //     $this->user->userGroups()->attach($userGroup);

    //     AssetAccessGrant::factory()->create([
    //         'asset_id' => $this->asset->id,
    //         'user_group_id' => $userGroup->id,
    //         'role' => AssetAccessRole::REQUESTER,
    //     ]);

    //     $this->giveUserPermission($this->user, 'sessionaudit:view');

    //     $this->assertTrue($this->policy->view($this->user, $this->sessionAudit));
    // }

    // public function test_view_method_uses_or_logic_correctly(): void
    // {
    //     // Test that view() returns true if user has viewany permission
    //     $viewAnyUser = User::factory()->create();
    //     $this->giveUserPermission($viewAnyUser, 'sessionaudit:viewany');
    //     $this->assertTrue($this->policy->view($viewAnyUser, $this->sessionAudit));

    //     // Test that view() returns true if user has both asset access and view permission
    //     $assetViewUser = User::factory()->create();
    //     $this->giveUserAssetAccess($assetViewUser, $this->asset);
    //     $this->giveUserPermission($assetViewUser, 'sessionaudit:view');
    //     $this->assertTrue($this->policy->view($assetViewUser, $this->sessionAudit));
    // }

    // public function test_view_with_different_assets(): void
    // {
    //     $asset1 = Asset::factory()->create();
    //     $asset2 = Asset::factory()->create();
    //     $session1 = Session::factory()->create([
    //         'org_id' => $this->org->id,
    //         'asset_id' => $asset1->id,
    //         'request_id' => $this->request->id,
    //         'requester_id' => $this->user->id,
    //         'approver_id' => $this->approver->id,
    //         'start_datetime' => now(),
    //         'end_datetime' => now()->addHour(),
    //         'scheduled_end_datetime' => now()->addHour(),
    //         'requested_duration' => 60,
    //         'actual_duration' => 60,
    //     ]);
    //     $session2 = Session::factory()->create([
    //         'org_id' => $this->org->id,
    //         'asset_id' => $asset2->id,
    //         'request_id' => $this->request->id,
    //         'requester_id' => $this->user->id,
    //         'approver_id' => $this->approver->id,
    //         'start_datetime' => now(),
    //         'end_datetime' => now()->addHour(),
    //         'scheduled_end_datetime' => now()->addHour(),
    //         'requested_duration' => 60,
    //         'actual_duration' => 60,
    //     ]);
    //     $sessionAudit1 = SessionAudit::factory()->create([
    //         'org_id' => $this->org->id,
    //         'session_id' => $session1->id,
    //         'request_id' => $this->request->id,
    //         'asset_id' => $asset1->id,
    //     ]);
    //     $sessionAudit2 = SessionAudit::factory()->create([
    //         'org_id' => $this->org->id,
    //         'session_id' => $session2->id,
    //         'request_id' => $this->request->id,
    //         'asset_id' => $asset2->id,
    //     ]);

    //     $this->giveUserAssetAccess($this->user, $asset1);
    //     $this->giveUserPermission($this->user, 'sessionaudit:view');

    //     // User should only be able to view session audit for asset they have access to
    //     $this->assertTrue($this->policy->view($this->user, $sessionAudit1));
    //     $this->assertFalse($this->policy->view($this->user, $sessionAudit2));
    // }

    // public function test_multiple_users_with_different_permissions(): void
    // {
    //     $viewAnyUser = User::factory()->create();
    //     $assetViewUser = User::factory()->create();
    //     $noPermissionUser = User::factory()->create();

    //     $this->giveUserPermission($viewAnyUser, 'sessionaudit:viewany');

    //     $this->giveUserAssetAccess($assetViewUser, $this->asset);
    //     $this->giveUserPermission($assetViewUser, 'sessionaudit:view');

    //     // ViewAny user
    //     $this->assertTrue($this->policy->viewAny($viewAnyUser));
    //     $this->assertTrue($this->policy->view($viewAnyUser, $this->sessionAudit));

    //     // Asset view user
    //     $this->assertFalse($this->policy->viewAny($assetViewUser));
    //     $this->assertTrue($this->policy->view($assetViewUser, $this->sessionAudit));

    //     // No permission user
    //     $this->assertFalse($this->policy->viewAny($noPermissionUser));
    //     $this->assertFalse($this->policy->view($noPermissionUser, $this->sessionAudit));
    // }

    // public function test_permission_names_are_case_insensitive(): void
    // {
    //     $this->giveUserPermission($this->user, 'SESSIONAUDIT:VIEWANY');

    //     $this->assertTrue($this->policy->viewAny($this->user));
    // }

    // public function test_viewany_and_view_permissions_are_independent(): void
    // {
    //     // User with only view permission should not have viewAny access
    //     $viewOnlyUser = User::factory()->create();
    //     $this->giveUserPermission($viewOnlyUser, 'sessionaudit:view');

    //     $this->assertFalse($this->policy->viewAny($viewOnlyUser));
    //     $this->assertFalse($this->policy->view($viewOnlyUser, $this->sessionAudit)); // No asset access

    //     // User with only viewAny permission should have both accesses
    //     $viewAnyOnlyUser = User::factory()->create();
    //     $this->giveUserPermission($viewAnyOnlyUser, 'sessionaudit:viewany');

    //     $this->assertTrue($this->policy->viewAny($viewAnyOnlyUser));
    //     $this->assertTrue($this->policy->view($viewAnyOnlyUser, $this->sessionAudit));
    // }

    // public function test_policy_handles_permission_edge_cases(): void
    // {
    //     // Test with similar but different permission names
    //     $this->giveUserPermission($this->user, 'sessionaudit:viewall'); // Note: 'viewall' not 'viewany'

    //     $this->assertFalse($this->policy->viewAny($this->user)); // Should not match
    //     $this->assertFalse($this->policy->view($this->user, $this->sessionAudit));
    // }

    // public function test_session_audit_access_depends_on_session_asset(): void
    // {
    //     // Create session audit for a different asset
    //     $otherAsset = Asset::factory()->create();
    //     $otherSession = Session::factory()->create([
    //         'org_id' => $this->org->id,
    //         'asset_id' => $otherAsset->id,
    //         'request_id' => $this->request->id,
    //         'requester_id' => $this->user->id,
    //         'approver_id' => $this->approver->id,
    //         'start_datetime' => now(),
    //         'end_datetime' => now()->addHour(),
    //         'scheduled_end_datetime' => now()->addHour(),
    //         'requested_duration' => 60,
    //         'actual_duration' => 60,
    //     ]);
    //     $otherSessionAudit = SessionAudit::factory()->create([
    //         'org_id' => $this->org->id,
    //         'session_id' => $otherSession->id,
    //         'request_id' => $this->request->id,
    //         'asset_id' => $otherAsset->id,
    //     ]);

    //     // Give user access to original asset only
    //     $this->giveUserAssetAccess($this->user, $this->asset);
    //     $this->giveUserPermission($this->user, 'sessionaudit:view');

    //     // Should have access to original session audit but not other
    //     $this->assertTrue($this->policy->view($this->user, $this->sessionAudit));
    //     $this->assertFalse($this->policy->view($this->user, $otherSessionAudit));
    // }

    // private function giveUserPermission(User $user, string $permissionName): void
    // {
    //     $permission = Permission::firstOrCreate(
    //         ['name' => $permissionName],
    //         ['description' => ucfirst(str_replace(':', ' ', $permissionName))]
    //     );
    //     $role = Role::factory()->create();
    //     $role->permissions()->attach($permission);
    //     $user->roles()->attach($role);
    //     $user->clearUserRolePermissionCache();
    // }

    // private function giveUserAssetAccess(User $user, Asset $asset): void
    // {
    //     AssetAccessGrant::factory()->create([
    //         'asset_id' => $asset->id,
    //         'user_id' => $user->id,
    //         'role' => AssetAccessRole::REQUESTER,
    //     ]);
    // }
}
