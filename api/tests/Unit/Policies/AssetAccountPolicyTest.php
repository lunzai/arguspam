<?php

namespace Tests\Unit\Policies;

use App\Enums\AssetAccessRole;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\AssetAccount;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Models\UserGroup;
use App\Policies\AssetAccountPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetAccountPolicyTest extends TestCase
{
    use RefreshDatabase;

    private AssetAccountPolicy $policy;
    private User $user;
    private Asset $asset;
    private AssetAccount $assetAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new AssetAccountPolicy;
        $this->user = User::factory()->create();
        $this->asset = Asset::factory()->create();
        $this->assetAccount = AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
        ]);
    }

    public function test_view_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'assetaccount:viewany');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
    }

    public function test_view_returns_true_when_user_has_viewany_permission(): void
    {
        $this->giveUserPermission($this->user, 'assetaccount:viewany');

        $this->assertTrue($this->policy->view($this->user, $this->assetAccount));
    }

    public function test_view_returns_true_when_user_has_asset_access_and_view_permission(): void
    {
        $this->giveUserAssetAccess($this->user, $this->asset);
        $this->giveUserPermission($this->user, 'assetaccount:view');

        $this->assertTrue($this->policy->view($this->user, $this->assetAccount));
    }

    public function test_view_returns_false_when_user_has_view_permission_but_no_asset_access(): void
    {
        $this->giveUserPermission($this->user, 'assetaccount:view');

        $this->assertFalse($this->policy->view($this->user, $this->assetAccount));
    }

    public function test_view_returns_false_when_user_has_asset_access_but_no_view_permission(): void
    {
        $this->giveUserAssetAccess($this->user, $this->asset);

        $this->assertFalse($this->policy->view($this->user, $this->assetAccount));
    }

    public function test_view_returns_false_when_user_lacks_both_conditions(): void
    {
        $this->assertFalse($this->policy->view($this->user, $this->assetAccount));
    }

    public function test_view_with_group_based_asset_access(): void
    {
        $userGroup = UserGroup::factory()->create();
        $this->user->userGroups()->attach($userGroup);

        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_group_id' => $userGroup->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $this->giveUserPermission($this->user, 'assetaccount:view');

        $this->assertTrue($this->policy->view($this->user, $this->assetAccount));
    }

    public function test_create_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'assetaccount:create');

        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_create_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->create($this->user));
    }

    public function test_update_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'assetaccount:updateany');

        $this->assertTrue($this->policy->updateAny($this->user));
    }

    public function test_update_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->updateAny($this->user));
    }

    public function test_delete_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'assetaccount:deleteany');

        $this->assertTrue($this->policy->deleteAny($this->user));
    }

    public function test_delete_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->deleteAny($this->user));
    }

    public function test_restore_any_returns_true_when_user_has_permission(): void
    {
        $this->giveUserPermission($this->user, 'assetaccount:restoreany');

        $this->assertTrue($this->policy->restoreAny($this->user));
    }

    public function test_restore_any_returns_false_when_user_lacks_permission(): void
    {
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_user_with_all_permissions_can_perform_all_actions(): void
    {
        $this->giveUserPermission($this->user, 'assetaccount:viewany');
        $this->giveUserPermission($this->user, 'assetaccount:view');
        $this->giveUserPermission($this->user, 'assetaccount:create');
        $this->giveUserPermission($this->user, 'assetaccount:updateany');
        $this->giveUserPermission($this->user, 'assetaccount:deleteany');
        $this->giveUserPermission($this->user, 'assetaccount:restoreany');
        $this->giveUserAssetAccess($this->user, $this->asset);

        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->view($this->user, $this->assetAccount));
        $this->assertTrue($this->policy->create($this->user));
        $this->assertTrue($this->policy->updateAny($this->user));
        $this->assertTrue($this->policy->deleteAny($this->user));
        $this->assertTrue($this->policy->restoreAny($this->user));
    }

    public function test_user_with_no_permissions_cannot_perform_any_actions(): void
    {
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->view($this->user, $this->assetAccount));
        $this->assertFalse($this->policy->create($this->user));
        $this->assertFalse($this->policy->updateAny($this->user));
        $this->assertFalse($this->policy->deleteAny($this->user));
        $this->assertFalse($this->policy->restoreAny($this->user));
    }

    public function test_view_method_uses_or_logic_correctly(): void
    {
        // Test that view() returns true if user has viewany permission
        $viewAnyUser = User::factory()->create();
        $this->giveUserPermission($viewAnyUser, 'assetaccount:viewany');
        $this->assertTrue($this->policy->view($viewAnyUser, $this->assetAccount));

        // Test that view() returns true if user has both asset access and view permission
        $assetViewUser = User::factory()->create();
        $this->giveUserAssetAccess($assetViewUser, $this->asset);
        $this->giveUserPermission($assetViewUser, 'assetaccount:view');
        $this->assertTrue($this->policy->view($assetViewUser, $this->assetAccount));
    }

    public function test_multiple_users_with_different_permissions(): void
    {
        $viewAnyUser = User::factory()->create();
        $assetViewUser = User::factory()->create();
        $createUser = User::factory()->create();
        $updateUser = User::factory()->create();
        $deleteUser = User::factory()->create();
        $restoreUser = User::factory()->create();

        $this->giveUserPermission($viewAnyUser, 'assetaccount:viewany');

        $this->giveUserAssetAccess($assetViewUser, $this->asset);
        $this->giveUserPermission($assetViewUser, 'assetaccount:view');

        $this->giveUserPermission($createUser, 'assetaccount:create');
        $this->giveUserPermission($updateUser, 'assetaccount:updateany');
        $this->giveUserPermission($deleteUser, 'assetaccount:deleteany');
        $this->giveUserPermission($restoreUser, 'assetaccount:restoreany');

        // ViewAny user
        $this->assertTrue($this->policy->viewAny($viewAnyUser));
        $this->assertTrue($this->policy->view($viewAnyUser, $this->assetAccount));
        $this->assertFalse($this->policy->create($viewAnyUser));

        // Asset view user
        $this->assertFalse($this->policy->viewAny($assetViewUser));
        $this->assertTrue($this->policy->view($assetViewUser, $this->assetAccount));
        $this->assertFalse($this->policy->create($assetViewUser));

        // Create user
        $this->assertFalse($this->policy->viewAny($createUser));
        $this->assertFalse($this->policy->view($createUser, $this->assetAccount));
        $this->assertTrue($this->policy->create($createUser));

        // Other users
        $this->assertTrue($this->policy->updateAny($updateUser));
        $this->assertTrue($this->policy->deleteAny($deleteUser));
        $this->assertTrue($this->policy->restoreAny($restoreUser));
    }

    public function test_view_with_different_assets(): void
    {
        $asset1 = Asset::factory()->create();
        $asset2 = Asset::factory()->create();
        $assetAccount1 = AssetAccount::factory()->create(['asset_id' => $asset1->id]);
        $assetAccount2 = AssetAccount::factory()->create(['asset_id' => $asset2->id]);

        $this->giveUserAssetAccess($this->user, $asset1);
        $this->giveUserPermission($this->user, 'assetaccount:view');

        // User should only be able to view asset account for asset they have access to
        $this->assertTrue($this->policy->view($this->user, $assetAccount1));
        $this->assertFalse($this->policy->view($this->user, $assetAccount2));
    }

    public function test_permission_names_are_case_insensitive(): void
    {
        $this->giveUserPermission($this->user, 'ASSETACCOUNT:VIEWANY');

        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_permissions_are_independent(): void
    {
        // Each permission should only grant access to its specific action
        $this->giveUserPermission($this->user, 'assetaccount:create');

        $this->assertTrue($this->policy->create($this->user));
        $this->assertFalse($this->policy->viewAny($this->user));
        $this->assertFalse($this->policy->view($this->user, $this->assetAccount));
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

    private function giveUserAssetAccess(User $user, Asset $asset): void
    {
        AssetAccessGrant::factory()->create([
            'asset_id' => $asset->id,
            'user_id' => $user->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);
    }
}
