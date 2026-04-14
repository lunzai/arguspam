<?php

namespace Tests\Integration\Controllers\User;

use App\Enums\AssetAccessRole;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAssetControllerTest extends TestCase
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
    // GET /users/me/assets — index
    // -------------------------------------------------------------------------

    public function test_index_returns_requestable_assets(): void
    {
        $this->giveUserPermission($this->user, 'asset:viewrequestable');
        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $this->user->id,
            'user_group_id' => null,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/users/me/assets')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);

        $ids = collect($response->json('data'))->pluck('attributes.id');
        $this->assertTrue($ids->contains($this->asset->id));
    }

    public function test_index_returns_empty_when_no_grants(): void
    {
        $this->giveUserPermission($this->user, 'asset:viewrequestable');

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/users/me/assets')
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/users/me/assets')
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // GET /users/me/assets/{asset} — show
    // -------------------------------------------------------------------------

    public function test_show_returns_200_when_user_has_requester_grant(): void
    {
        $this->giveUserPermission($this->user, 'asset:viewrequestable');
        AssetAccessGrant::factory()->create([
            'asset_id' => $this->asset->id,
            'user_id' => $this->user->id,
            'user_group_id' => null,
            'role' => AssetAccessRole::REQUESTER,
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/users/me/assets/{$this->asset->id}")
            ->assertStatus(200);
    }

    public function test_show_returns_401_without_requester_grant(): void
    {
        $this->giveUserPermission($this->user, 'asset:viewrequestable');

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/users/me/assets/{$this->asset->id}")
            ->assertStatus(401);
    }
}
