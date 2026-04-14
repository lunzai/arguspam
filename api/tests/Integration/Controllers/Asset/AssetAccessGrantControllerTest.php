<?php

namespace Tests\Integration\Controllers\Asset;

use App\Enums\AssetAccessRole;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\Org;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetAccessGrantControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Org $org;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);
    }

    // -------------------------------------------------------------------------
    // POST /assets/{asset}/access-grant — store
    // -------------------------------------------------------------------------

    public function test_store_adds_user_as_requester(): void
    {
        $this->giveUserPermission($this->user, 'asset:addaccessgrant');
        $target = User::factory()->create();
        $this->org->users()->attach($target->id);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson("/assets/{$this->asset->id}/access-grant", [
                'user_ids' => [$target->id],
                'role' => AssetAccessRole::REQUESTER->value,
            ])
            ->assertStatus(201);

        $this->assertTrue(
            AssetAccessGrant::where('asset_id', $this->asset->id)
                ->where('user_id', $target->id)
                ->where('role', AssetAccessRole::REQUESTER->value)
                ->exists(),
        );
    }

    public function test_store_adds_user_group_as_approver(): void
    {
        $this->giveUserPermission($this->user, 'asset:addaccessgrant');
        $group = UserGroup::factory()->create(['org_id' => $this->org->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson("/assets/{$this->asset->id}/access-grant", [
                'user_group_ids' => [$group->id],
                'role' => AssetAccessRole::APPROVER->value,
            ])
            ->assertStatus(201);

        $this->assertTrue(
            AssetAccessGrant::where('asset_id', $this->asset->id)
                ->where('user_group_id', $group->id)
                ->where('role', AssetAccessRole::APPROVER->value)
                ->exists(),
        );
    }

    public function test_store_returns_403_without_permission(): void
    {
        $target = User::factory()->create();
        $this->org->users()->attach($target->id);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson("/assets/{$this->asset->id}/access-grant", [
                'user_ids' => [$target->id],
                'role' => AssetAccessRole::REQUESTER->value,
            ])
            ->assertStatus(403);
    }

    public function test_store_returns_422_when_role_is_invalid(): void
    {
        $this->giveUserPermission($this->user, 'asset:addaccessgrant');
        $target = User::factory()->create();

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson("/assets/{$this->asset->id}/access-grant", [
                'user_ids' => [$target->id],
                'role' => 'invalid_role',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['role']);
    }

    // -------------------------------------------------------------------------
    // DELETE /assets/{asset}/access-grant — destroy
    // -------------------------------------------------------------------------

    public function test_destroy_removes_user_grant(): void
    {
        $this->giveUserPermission($this->user, 'asset:removeaccessgrant');
        $target = User::factory()->create();
        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $target->id,
            'user_group_id' => null,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->deleteJson("/assets/{$this->asset->id}/access-grant", [
                'role' => AssetAccessRole::REQUESTER->value,
                'type' => 'user',
                'user_id' => $target->id,
            ])
            ->assertStatus(204);

        $this->assertFalse(
            AssetAccessGrant::where('asset_id', $this->asset->id)
                ->where('user_id', $target->id)
                ->exists(),
        );
    }

    public function test_destroy_removes_user_group_grant(): void
    {
        $this->giveUserPermission($this->user, 'asset:removeaccessgrant');
        $group = UserGroup::factory()->create(['org_id' => $this->org->id]);
        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => null,
            'user_group_id' => $group->id,
            'role' => AssetAccessRole::APPROVER,
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->deleteJson("/assets/{$this->asset->id}/access-grant", [
                'role' => AssetAccessRole::APPROVER->value,
                'type' => 'user_group',
                'user_group_id' => $group->id,
            ])
            ->assertStatus(204);

        $this->assertFalse(
            AssetAccessGrant::where('asset_id', $this->asset->id)
                ->where('user_group_id', $group->id)
                ->exists(),
        );
    }

    public function test_destroy_returns_403_without_permission(): void
    {
        $target = User::factory()->create();
        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $target->id,
            'user_group_id' => null,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->deleteJson("/assets/{$this->asset->id}/access-grant", [
                'role' => AssetAccessRole::REQUESTER->value,
                'type' => 'user',
                'user_id' => $target->id,
            ])
            ->assertStatus(403);
    }

    public function test_destroy_returns_422_for_invalid_type(): void
    {
        $this->giveUserPermission($this->user, 'asset:removeaccessgrant');

        $this->actingAsWithOrg($this->user, $this->org)
            ->deleteJson("/assets/{$this->asset->id}/access-grant", [
                'role' => AssetAccessRole::REQUESTER->value,
                'type' => 'invalid_type',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }
}
