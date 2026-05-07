<?php

namespace Tests\Integration\Controllers\Asset;

use App\Models\Asset;
use App\Models\Org;
use App\Models\User;
use App\Services\Jit\JitManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetConnectionControllerTest extends TestCase
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
    // GET /assets/{asset}/connection — show
    // -------------------------------------------------------------------------

    public function test_show_returns_200_when_connection_succeeds(): void
    {
        $this->giveUserPermission($this->user, 'asset:view');
        $this->mock(JitManager::class, function ($mock) {
            $mock->shouldReceive('getAdminCredentials')->once()->andReturn([
                'username' => 'admin',
                'password' => 'secret',
            ]);
            $mock->shouldReceive('testConnection')->once()->andReturn(true);
        });

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/assets/{$this->asset->id}/connection")
            ->assertStatus(200);
    }

    public function test_show_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/assets/{$this->asset->id}/connection")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // GET /assets/{asset}/databases — index
    // -------------------------------------------------------------------------

    public function test_index_returns_databases_list(): void
    {
        $this->giveUserPermission($this->user, 'asset:view');
        $this->mock(JitManager::class, function ($mock) {
            $mock->shouldReceive('getAllDatabases')->once()->andReturn(['db1', 'db2']);
        });

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/assets/{$this->asset->id}/databases")
            ->assertStatus(200)
            ->assertJsonStructure(['data']);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/assets/{$this->asset->id}/databases")
            ->assertStatus(403);
    }
}
