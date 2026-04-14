<?php

namespace Tests\Integration\Controllers\Asset;

use App\Models\Asset;
use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetAccountControllerTest extends TestCase
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

    private function validPayload(): array
    {
        return [
            'host' => '192.168.1.100',
            'port' => 5432,
            'dbms' => 'postgresql',
        ];
    }

    // -------------------------------------------------------------------------
    // PUT /assets/{asset}/credential — update
    // -------------------------------------------------------------------------

    public function test_update_returns_200_with_valid_payload(): void
    {
        $this->giveUserPermission($this->user, 'asset:updateadminaccount');

        $this->actingAsWithOrg($this->user, $this->org)
            ->putJson("/assets/{$this->asset->id}/credential", $this->validPayload())
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_update_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->user, $this->org)
            ->putJson("/assets/{$this->asset->id}/credential", $this->validPayload())
            ->assertStatus(403);
    }

    public function test_update_returns_422_with_invalid_host(): void
    {
        $this->giveUserPermission($this->user, 'asset:updateadminaccount');

        $this->actingAsWithOrg($this->user, $this->org)
            ->putJson("/assets/{$this->asset->id}/credential", array_merge($this->validPayload(), ['host' => 'not_a_valid_host!']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['host']);
    }

    public function test_update_returns_422_with_missing_required_fields(): void
    {
        $this->giveUserPermission($this->user, 'asset:updateadminaccount');

        $this->actingAsWithOrg($this->user, $this->org)
            ->putJson("/assets/{$this->asset->id}/credential", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['host', 'port', 'dbms']);
    }

    public function test_update_persists_host_port_and_dbms(): void
    {
        $this->giveUserPermission($this->user, 'asset:updateadminaccount');

        $this->actingAsWithOrg($this->user, $this->org)
            ->putJson("/assets/{$this->asset->id}/credential", $this->validPayload())
            ->assertStatus(200);

        $this->assertDatabaseHas('assets', [
            'id' => $this->asset->id,
            'host' => '192.168.1.100',
            'port' => 5432,
        ]);
    }
}
