<?php

namespace Tests\Integration\Controllers\Asset;

use App\Enums\Dbms;
use App\Enums\Status;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
    }

    private function validAssetPayload(): array
    {
        return [
            'org_id' => $this->org->id,
            'name' => 'Test-DB - UAT',
            'description' => 'A test database asset',
            'status' => Status::ACTIVE->value,
            'host' => '127.0.0.1',
            'port' => 3306,
            'dbms' => Dbms::MYSQL->value,
            'username' => 'admin_user',
            'password' => 'SecretPass123',
        ];
    }

    // -------------------------------------------------------------------------
    // GET /assets — index
    // -------------------------------------------------------------------------

    public function test_index_lists_assets_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'asset:view');
        Asset::factory()->create(['org_id' => $this->org->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/assets')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/assets')
            ->assertStatus(403);
    }

    public function test_index_returns_400_without_org_header(): void
    {
        $this->giveUserPermission($this->user, 'asset:view');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/assets')
            ->assertStatus(400);
    }

    public function test_index_is_scoped_to_current_org(): void
    {
        $this->giveUserPermission($this->user, 'asset:view');
        $otherOrg = Org::factory()->create();
        Asset::factory()->create(['org_id' => $this->org->id]);
        Asset::factory()->create(['org_id' => $otherOrg->id]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/assets')
            ->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertFalse(
            Asset::where('org_id', $otherOrg->id)->whereIn('id', $ids)->exists(),
        );
    }

    // -------------------------------------------------------------------------
    // POST /assets — store
    // -------------------------------------------------------------------------

    public function test_store_creates_asset_with_admin_account(): void
    {
        $this->giveUserPermission($this->user, 'asset:create');

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/assets', $this->validAssetPayload())
            ->assertStatus(201);

        $asset = Asset::where('org_id', $this->org->id)->where('name', 'Test-DB - UAT')->first();
        $this->assertNotNull($asset);
        $this->assertTrue(
            AssetAccount::where('asset_id', $asset->id)->where('type', 'admin')->exists(),
        );
    }

    public function test_store_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/assets', $this->validAssetPayload())
            ->assertStatus(403);
    }

    public function test_store_returns_422_when_name_is_missing(): void
    {
        $this->giveUserPermission($this->user, 'asset:create');
        $payload = $this->validAssetPayload();
        unset($payload['name']);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/assets', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_returns_422_when_name_is_too_short(): void
    {
        $this->giveUserPermission($this->user, 'asset:create');
        $payload = array_merge($this->validAssetPayload(), ['name' => 'x']);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/assets', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_returns_422_for_invalid_dbms(): void
    {
        $this->giveUserPermission($this->user, 'asset:create');
        $payload = array_merge($this->validAssetPayload(), ['dbms' => 'invalid_db']);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/assets', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['dbms']);
    }

    public function test_store_returns_422_when_port_is_out_of_range(): void
    {
        $this->giveUserPermission($this->user, 'asset:create');
        $payload = array_merge($this->validAssetPayload(), ['port' => 99999]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/assets', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['port']);
    }

    public function test_store_returns_422_when_credentials_are_missing(): void
    {
        $this->giveUserPermission($this->user, 'asset:create');
        $payload = $this->validAssetPayload();
        unset($payload['username'], $payload['password']);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/assets', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['username', 'password']);
    }

    // -------------------------------------------------------------------------
    // GET /assets/{id} — show
    // -------------------------------------------------------------------------

    public function test_show_returns_asset_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'asset:view');
        $asset = Asset::factory()->create(['org_id' => $this->org->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/assets/{$asset->id}")
            ->assertStatus(200)
            ->assertJsonPath('data.attributes.id', $asset->id);
    }

    public function test_show_returns_404_for_other_orgs_asset(): void
    {
        $this->giveUserPermission($this->user, 'asset:view');
        $otherOrg = Org::factory()->create();
        $asset = Asset::factory()->create(['org_id' => $otherOrg->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/assets/{$asset->id}")
            ->assertStatus(404);
    }

    public function test_show_returns_404_for_nonexistent_asset(): void
    {
        $this->giveUserPermission($this->user, 'asset:view');

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/assets/999999')
            ->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // PUT /assets/{id} — update
    // -------------------------------------------------------------------------

    public function test_update_modifies_asset(): void
    {
        $this->giveUserPermission($this->user, 'asset:update');
        $asset = Asset::factory()->create(['org_id' => $this->org->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->putJson("/assets/{$asset->id}", ['name' => 'Updated-DB - UAT'])
            ->assertStatus(200);

        $this->assertEquals('Updated-DB - UAT', $asset->fresh()->name);
    }

    public function test_update_returns_403_without_permission(): void
    {
        $asset = Asset::factory()->create(['org_id' => $this->org->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->putJson("/assets/{$asset->id}", ['name' => 'Updated-DB - UAT'])
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DELETE /assets/{id} — destroy
    // -------------------------------------------------------------------------

    public function test_destroy_soft_deletes_asset(): void
    {
        $this->giveUserPermission($this->user, 'asset:delete');
        $asset = Asset::factory()->create(['org_id' => $this->org->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->deleteJson("/assets/{$asset->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
    }

    public function test_destroy_returns_403_without_permission(): void
    {
        $asset = Asset::factory()->create(['org_id' => $this->org->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->deleteJson("/assets/{$asset->id}")
            ->assertStatus(403);
    }

    public function test_destroy_returns_401_when_unauthenticated(): void
    {
        $asset = Asset::factory()->create(['org_id' => $this->org->id]);

        $this->deleteJson("/assets/{$asset->id}")
            ->assertStatus(401);
    }
}
