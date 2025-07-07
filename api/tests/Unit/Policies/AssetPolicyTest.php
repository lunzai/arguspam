<?php

namespace Tests\Unit\Policies;

use App\Enums\AssetAccessRole;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\User;
use App\Models\UserGroup;
use App\Policies\AssetPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetPolicyTest extends TestCase
{
    use RefreshDatabase;

    private AssetPolicy $policy;
    private User $user;
    private User $requesterUser;
    private User $approverUser;
    private User $groupMember;
    private Asset $asset;
    private UserGroup $userGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new AssetPolicy;
        $this->user = User::factory()->create();
        $this->requesterUser = User::factory()->create();
        $this->approverUser = User::factory()->create();
        $this->groupMember = User::factory()->create();
        $this->asset = Asset::factory()->create();
        $this->userGroup = UserGroup::factory()->create();

        // Add groupMember to the userGroup
        $this->groupMember->userGroups()->attach($this->userGroup);
    }

    public function test_request_returns_true_when_user_has_direct_requester_access(): void
    {
        AssetAccessGrant::factory()->create([
            'user_id' => $this->requesterUser->id,
            'asset_id' => $this->asset->id,
            'role' => AssetAccessRole::REQUESTER,
            'user_group_id' => null,
        ]);

        $this->assertTrue($this->policy->request($this->requesterUser, $this->asset));
    }

    public function test_request_returns_true_when_user_has_group_requester_access(): void
    {
        AssetAccessGrant::factory()->create([
            'user_id' => null,
            'user_group_id' => $this->userGroup->id,
            'asset_id' => $this->asset->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $this->assertTrue($this->policy->request($this->groupMember, $this->asset));
    }

    public function test_request_returns_false_when_user_has_no_requester_access(): void
    {
        $this->assertFalse($this->policy->request($this->user, $this->asset));
    }

    public function test_request_returns_false_when_user_has_only_approver_access(): void
    {
        AssetAccessGrant::factory()->create([
            'user_id' => $this->approverUser->id,
            'asset_id' => $this->asset->id,
            'role' => AssetAccessRole::APPROVER,
            'user_group_id' => null,
        ]);

        $this->assertFalse($this->policy->request($this->approverUser, $this->asset));
    }

    public function test_approve_returns_true_when_user_has_direct_approver_access(): void
    {
        AssetAccessGrant::factory()->create([
            'user_id' => $this->approverUser->id,
            'asset_id' => $this->asset->id,
            'role' => AssetAccessRole::APPROVER,
            'user_group_id' => null,
        ]);

        $this->assertTrue($this->policy->approve($this->approverUser, $this->asset));
    }

    public function test_approve_returns_true_when_user_has_group_approver_access(): void
    {
        AssetAccessGrant::factory()->create([
            'user_id' => null,
            'user_group_id' => $this->userGroup->id,
            'asset_id' => $this->asset->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        $this->assertTrue($this->policy->approve($this->groupMember, $this->asset));
    }

    public function test_approve_returns_false_when_user_has_no_approver_access(): void
    {
        $this->assertFalse($this->policy->approve($this->user, $this->asset));
    }

    public function test_approve_returns_false_when_user_has_only_requester_access(): void
    {
        AssetAccessGrant::factory()->create([
            'user_id' => $this->requesterUser->id,
            'asset_id' => $this->asset->id,
            'role' => AssetAccessRole::REQUESTER,
            'user_group_id' => null,
        ]);

        $this->assertFalse($this->policy->approve($this->requesterUser, $this->asset));
    }

    public function test_view_returns_true_when_user_can_request(): void
    {
        AssetAccessGrant::factory()->create([
            'user_id' => $this->requesterUser->id,
            'asset_id' => $this->asset->id,
            'role' => AssetAccessRole::REQUESTER,
            'user_group_id' => null,
        ]);

        $this->assertTrue($this->policy->view($this->requesterUser, $this->asset));
    }

    public function test_view_returns_true_when_user_can_approve(): void
    {
        AssetAccessGrant::factory()->create([
            'user_id' => $this->approverUser->id,
            'asset_id' => $this->asset->id,
            'role' => AssetAccessRole::APPROVER,
            'user_group_id' => null,
        ]);

        $this->assertTrue($this->policy->view($this->approverUser, $this->asset));
    }

    public function test_view_returns_true_when_user_has_both_roles(): void
    {
        AssetAccessGrant::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $this->asset->id,
            'role' => AssetAccessRole::REQUESTER,
            'user_group_id' => null,
        ]);

        AssetAccessGrant::factory()->create([
            'user_id' => $this->user->id,
            'asset_id' => $this->asset->id,
            'role' => AssetAccessRole::APPROVER,
            'user_group_id' => null,
        ]);

        $this->assertTrue($this->policy->view($this->user, $this->asset));
    }

    public function test_view_returns_false_when_user_has_no_access(): void
    {
        $this->assertFalse($this->policy->view($this->user, $this->asset));
    }

    public function test_has_access_grant_works_with_multiple_user_groups(): void
    {
        // Create another user group
        $anotherUserGroup = UserGroup::factory()->create();
        $anotherGroupMember = User::factory()->create();
        $anotherGroupMember->userGroups()->attach($anotherUserGroup);

        // Give first group requester access
        AssetAccessGrant::factory()->create([
            'user_id' => null,
            'user_group_id' => $this->userGroup->id,
            'asset_id' => $this->asset->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        // Give second group approver access
        AssetAccessGrant::factory()->create([
            'user_id' => null,
            'user_group_id' => $anotherUserGroup->id,
            'asset_id' => $this->asset->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        // First group member can request but not approve
        $this->assertTrue($this->policy->request($this->groupMember, $this->asset));
        $this->assertFalse($this->policy->approve($this->groupMember, $this->asset));

        // Second group member can approve but not request
        $this->assertFalse($this->policy->request($anotherGroupMember, $this->asset));
        $this->assertTrue($this->policy->approve($anotherGroupMember, $this->asset));
    }

    public function test_user_with_both_direct_and_group_access(): void
    {
        // Give user direct requester access
        AssetAccessGrant::factory()->create([
            'user_id' => $this->groupMember->id,
            'asset_id' => $this->asset->id,
            'role' => AssetAccessRole::REQUESTER,
            'user_group_id' => null,
        ]);

        // Give user's group approver access
        AssetAccessGrant::factory()->create([
            'user_id' => null,
            'user_group_id' => $this->userGroup->id,
            'asset_id' => $this->asset->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        // User should have both permissions
        $this->assertTrue($this->policy->request($this->groupMember, $this->asset));
        $this->assertTrue($this->policy->approve($this->groupMember, $this->asset));
        $this->assertTrue($this->policy->view($this->groupMember, $this->asset));
    }

    public function test_access_grant_for_different_asset_does_not_work(): void
    {
        $anotherAsset = Asset::factory()->create();

        AssetAccessGrant::factory()->create([
            'user_id' => $this->requesterUser->id,
            'asset_id' => $anotherAsset->id,
            'role' => AssetAccessRole::REQUESTER,
            'user_group_id' => null,
        ]);

        // Should not have access to the original asset
        $this->assertFalse($this->policy->request($this->requesterUser, $this->asset));
        $this->assertFalse($this->policy->approve($this->requesterUser, $this->asset));
        $this->assertFalse($this->policy->view($this->requesterUser, $this->asset));
    }
}
