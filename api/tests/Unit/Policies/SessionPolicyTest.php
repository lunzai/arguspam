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
use App\Models\User;
use App\Models\UserGroup;
use App\Policies\SessionPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionPolicyTest extends TestCase
{
    use RefreshDatabase;

    private SessionPolicy $policy;
    private User $user;
    private User $requester;
    private User $approver;
    private Org $org;
    private Asset $asset;
    private RequestModel $request;
    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new SessionPolicy;
        $this->user = User::factory()->create();
        $this->requester = User::factory()->create();
        $this->approver = User::factory()->create();
        $this->org = Org::factory()->create();
        $this->asset = Asset::factory()->create();
        $this->request = RequestModel::factory()->create();
        $this->session = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'request_id' => $this->request->id,
            'requester_id' => $this->requester->id,
            'approver_id' => $this->approver->id,
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
            'scheduled_end_datetime' => now()->addHour(),
            'requested_duration' => 60,
            'actual_duration' => 60,
        ]);
    }

    public function test_view_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:viewany');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_view_returns_true_when_user_has_viewany_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:viewany');

        $this->assertTrue($this->policy->view($this->user, $this->session));
    }

    public function test_view_returns_true_when_user_is_requester_with_view_permission(): void
    {
        $this->giveUserPermission($this->requester, 'session:view');

        $this->assertTrue($this->policy->view($this->requester, $this->session));
    }

    public function test_view_returns_true_when_user_can_approve_asset_with_view_permission(): void
    {
        $this->giveUserAssetAccess($this->user, $this->asset, AssetAccessRole::APPROVER);
        $this->giveUserPermission($this->user, 'session:view');

        $this->assertTrue($this->policy->view($this->user, $this->session));
    }

    public function test_view_returns_false_when_user_is_not_requester_and_cannot_approve(): void
    {
        $this->giveUserPermission($this->user, 'session:view');

        $this->assertFalse($this->policy->view($this->user, $this->session));
    }

    public function test_audit_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:auditany');

        $this->assertTrue($this->policy->auditAny($this->user));
    }

    public function test_audit_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->auditAny($this->user));
    }

    public function test_update_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:updateany');

        $this->assertTrue($this->policy->updateAny($this->user));
    }

    public function test_update_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->updateAny($this->user));
    }

    public function test_update_returns_true_when_user_has_updateany_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:updateany');

        $this->assertTrue($this->policy->update($this->user, $this->session));
    }

    public function test_update_returns_true_when_user_can_approve_asset_with_update_permission(): void
    {
        $this->giveUserAssetAccess($this->user, $this->asset, AssetAccessRole::APPROVER);
        $this->giveUserPermission($this->user, 'session:update');

        $this->assertTrue($this->policy->update($this->user, $this->session));
    }

    public function test_update_returns_false_when_user_cannot_approve_asset(): void
    {
        $this->giveUserPermission($this->user, 'session:update');

        $this->assertFalse($this->policy->update($this->user, $this->session));
    }

    public function test_terminate_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:terminateany');

        $this->assertTrue($this->policy->terminateAny($this->user));
    }

    public function test_terminate_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->terminateAny($this->user));
    }

    public function test_terminate_returns_true_when_user_has_terminateany_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:terminateany');

        $this->assertTrue($this->policy->terminate($this->user, $this->session));
    }

    public function test_terminate_returns_true_when_user_can_approve_and_is_not_requester(): void
    {
        $this->giveUserAssetAccess($this->user, $this->asset, AssetAccessRole::APPROVER);
        $this->giveUserPermission($this->user, 'session:terminate');

        $this->assertTrue($this->policy->terminate($this->user, $this->session));
    }

    public function test_terminate_returns_false_when_user_is_requester(): void
    {
        $this->giveUserAssetAccess($this->requester, $this->asset, AssetAccessRole::APPROVER);
        $this->giveUserPermission($this->requester, 'session:terminate');

        $this->assertFalse($this->policy->terminate($this->requester, $this->session));
    }

    public function test_retrieve_secret_returns_true_when_user_is_requester_with_permission(): void
    {
        $this->giveUserPermission($this->requester, 'session:retrievesecret');

        $this->assertTrue($this->policy->retrieveSecret($this->requester, $this->session));
    }

    public function test_retrieve_secret_returns_false_when_user_is_not_requester(): void
    {
        $this->giveUserPermission($this->user, 'session:retrievesecret');

        $this->assertFalse($this->policy->retrieveSecret($this->user, $this->session));
    }

    public function test_start_returns_true_when_user_is_requester_with_permission(): void
    {
        $this->giveUserPermission($this->requester, 'session:start');

        $this->assertTrue($this->policy->start($this->requester, $this->session));
    }

    public function test_start_returns_false_when_user_is_not_requester(): void
    {
        $this->giveUserPermission($this->user, 'session:start');

        $this->assertFalse($this->policy->start($this->user, $this->session));
    }

    public function test_end_returns_true_when_user_is_requester_with_permission(): void
    {
        $this->giveUserPermission($this->requester, 'session:end');

        $this->assertTrue($this->policy->end($this->requester, $this->session));
    }

    public function test_end_returns_false_when_user_is_not_requester(): void
    {
        $this->giveUserPermission($this->user, 'session:end');

        $this->assertFalse($this->policy->end($this->user, $this->session));
    }

    public function test_delete_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:deleteany');

        $this->assertTrue($this->policy->deleteAny($this->user));
    }

    public function test_delete_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->deleteAny($this->user));
    }

    public function test_restore_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'session:restoreany');

        $this->assertTrue($this->policy->restoreAny($this->user));
    }

    public function test_restore_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_view_with_group_based_asset_access(): void
    {
        $userGroup = UserGroup::factory()->create();
        $this->user->userGroups()->attach($userGroup);

        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_group_id' => $userGroup->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        $this->giveUserPermission($this->user, 'session:view');

        $this->assertTrue($this->policy->view($this->user, $this->session));
    }

    public function test_multiple_users_with_different_permissions(): void
    {
        $viewAnyUser = User::factory()->create();
        $requesterUser = User::factory()->create();
        $approverUser = User::factory()->create();

        // Create a session with specific requester
        $testSession = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'request_id' => $this->request->id,
            'requester_id' => $requesterUser->id,
            'approver_id' => $this->approver->id,
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
            'scheduled_end_datetime' => now()->addHour(),
            'requested_duration' => 60,
            'actual_duration' => 60,
        ]);

        $this->giveUserPermission($viewAnyUser, 'session:viewany');
        $this->giveUserPermission($requesterUser, 'session:view');
        $this->giveUserAssetAccess($approverUser, $this->asset, AssetAccessRole::APPROVER);
        $this->giveUserPermission($approverUser, 'session:view');

        // ViewAny user should have access
        $this->assertTrue($this->policy->view($viewAnyUser, $testSession));

        // Requester should have access
        $this->assertTrue($this->policy->view($requesterUser, $testSession));

        // Approver should have access
        $this->assertTrue($this->policy->view($approverUser, $testSession));

        // Regular user should not have access
        $regularUser = User::factory()->create();
        $this->giveUserPermission($regularUser, 'session:view');
        $this->assertFalse($this->policy->view($regularUser, $testSession));
    }

    public function test_requester_specific_actions(): void
    {
        $this->giveUserPermission($this->requester, 'session:retrievesecret');
        $this->giveUserPermission($this->requester, 'session:start');
        $this->giveUserPermission($this->requester, 'session:end');

        // Requester should be able to perform all actions
        $this->assertTrue($this->policy->retrieveSecret($this->requester, $this->session));
        $this->assertTrue($this->policy->start($this->requester, $this->session));
        $this->assertTrue($this->policy->end($this->requester, $this->session));

        // Other user with same permissions should not be able to perform these actions
        $this->giveUserPermission($this->user, 'session:retrievesecret');
        $this->giveUserPermission($this->user, 'session:start');
        $this->giveUserPermission($this->user, 'session:end');

        $this->assertFalse($this->policy->retrieveSecret($this->user, $this->session));
        $this->assertFalse($this->policy->start($this->user, $this->session));
        $this->assertFalse($this->policy->end($this->user, $this->session));
    }

    public function test_permission_names_are_case_insensitive(): void
    {
        $this->giveUserPermission($this->user, 'SESSION:VIEWANY');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_complex_terminate_logic(): void
    {
        // Approver with terminate permission should be able to terminate others' sessions
        $approver = User::factory()->create();
        $this->giveUserAssetAccess($approver, $this->asset, AssetAccessRole::APPROVER);
        $this->giveUserPermission($approver, 'session:terminate');

        $this->assertTrue($this->policy->terminate($approver, $this->session));

        // But requester cannot terminate their own session even with approver access
        $this->giveUserAssetAccess($this->requester, $this->asset, AssetAccessRole::APPROVER);
        $this->giveUserPermission($this->requester, 'session:terminate');

        $this->assertFalse($this->policy->terminate($this->requester, $this->session));
    }

    public function test_user_with_all_permissions_can_perform_all_actions(): void
    {
        $permissions = [
            'session:viewany', 'session:view', 'session:auditany', 'session:updateany',
            'session:update', 'session:terminateany', 'session:terminate',
            'session:retrievesecret', 'session:start', 'session:end',
            'session:deleteany', 'session:restoreany',
        ];

        foreach ($permissions as $permission) {
            $this->giveUserPermission($this->user, $permission);
        }

        $this->giveUserAssetAccess($this->user, $this->asset, AssetAccessRole::APPROVER);

        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->view($this->user, $this->session));
        $this->assertTrue($this->policy->auditAny($this->user));
        $this->assertTrue($this->policy->updateAny($this->user));
        $this->assertTrue($this->policy->update($this->user, $this->session));
        $this->assertTrue($this->policy->terminateAny($this->user));
        $this->assertTrue($this->policy->terminate($this->user, $this->session));
        $this->assertTrue($this->policy->deleteAny($this->user));
        $this->assertTrue($this->policy->restoreAny($this->user));

        // These should still be false because user is not the requester
        $this->assertFalse($this->policy->retrieveSecret($this->user, $this->session));
        $this->assertFalse($this->policy->start($this->user, $this->session));
        $this->assertFalse($this->policy->end($this->user, $this->session));
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

    private function giveUserAssetAccess(User $user, Asset $asset, AssetAccessRole $role): void
    {
        AssetAccessGrant::factory()->create([
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'role' => $role,
        ]);
    }
}
