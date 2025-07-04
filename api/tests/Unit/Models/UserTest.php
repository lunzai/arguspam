<?php

namespace Tests\Unit\Models;

use App\Enums\AssetAccessRole;
use App\Models\Asset;
use App\Models\Org;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->org = Org::factory()->create();
    }

    public function test_in_org_returns_true_when_user_belongs_to_org(): void
    {
        $this->user->orgs()->attach($this->org);

        $this->assertTrue($this->user->fresh()->inOrg($this->org));
    }

    public function test_in_org_returns_false_when_user_does_not_belong_to_org(): void
    {
        $this->assertFalse($this->user->inOrg($this->org));
    }

    public function test_all_requester_assets_returns_assets_with_requester_role(): void
    {
        $asset1 = Asset::factory()->create();
        $asset2 = Asset::factory()->create();
        $asset3 = Asset::factory()->create();

        // Direct user access
        $asset1->accessGrants()->create([
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        // Approver access (should not be included)
        $asset2->accessGrants()->create([
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        // Group access
        $userGroup = UserGroup::factory()->create();
        $this->user->userGroups()->attach($userGroup);

        $asset3->accessGrants()->create([
            'user_group_id' => $userGroup->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $requesterAssets = $this->user->fresh()->allRequesterAssets()->get();

        $this->assertCount(2, $requesterAssets);
        $this->assertTrue($requesterAssets->contains($asset1));
        $this->assertTrue($requesterAssets->contains($asset3));
        $this->assertFalse($requesterAssets->contains($asset2));
    }

    public function test_all_approver_assets_returns_assets_with_approver_role(): void
    {
        $asset1 = Asset::factory()->create();
        $asset2 = Asset::factory()->create();
        $asset3 = Asset::factory()->create();

        // Direct user access
        $asset1->accessGrants()->create([
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        // Requester access (should not be included)
        $asset2->accessGrants()->create([
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        // Group access
        $userGroup = UserGroup::factory()->create();
        $this->user->userGroups()->attach($userGroup);

        $asset3->accessGrants()->create([
            'user_group_id' => $userGroup->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        $approverAssets = $this->user->fresh()->allApproverAssets()->get();

        $this->assertCount(2, $approverAssets);
        $this->assertTrue($approverAssets->contains($asset1));
        $this->assertTrue($approverAssets->contains($asset3));
        $this->assertFalse($approverAssets->contains($asset2));
    }

    public function test_all_assets_returns_all_accessible_assets(): void
    {
        $asset1 = Asset::factory()->create();
        $asset2 = Asset::factory()->create();
        $asset3 = Asset::factory()->create();
        $asset4 = Asset::factory()->create();

        // Direct user access - requester
        $asset1->accessGrants()->create([
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        // Direct user access - approver
        $asset2->accessGrants()->create([
            'user_id' => $this->user->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        // Group access
        $userGroup = UserGroup::factory()->create();
        $this->user->userGroups()->attach($userGroup);

        $asset3->accessGrants()->create([
            'user_group_id' => $userGroup->id,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        // No access to asset4

        $allAssets = $this->user->fresh()->allAssets()->get();

        $this->assertCount(3, $allAssets);
        $this->assertTrue($allAssets->contains($asset1));
        $this->assertTrue($allAssets->contains($asset2));
        $this->assertTrue($allAssets->contains($asset3));
        $this->assertFalse($allAssets->contains($asset4));
    }

    public function test_user_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'name',
            'email',
            'status',
        ];

        $this->assertEquals($expectedFillable, $this->user->getFillable());
    }

    public function test_user_has_correct_hidden_attributes(): void
    {
        $expectedHidden = [
            'password',
            'remember_token',
            'deleted_at',
            'deleted_by',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ];

        $this->assertEquals($expectedHidden, $this->user->getHidden());
    }

    public function test_user_has_correct_casts(): void
    {
        $casts = $this->user->getCasts();

        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertArrayHasKey('last_login_at', $casts);
        $this->assertArrayHasKey('two_factor_enabled', $casts);
        $this->assertArrayHasKey('password', $casts);
        $this->assertArrayHasKey('status', $casts);
    }
}
