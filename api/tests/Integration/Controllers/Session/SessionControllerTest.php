<?php

namespace Tests\Integration\Controllers\Session;

use App\Models\Asset;
use App\Models\Org;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SessionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Org $org;
    private Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);
    }

    // -------------------------------------------------------------------------
    // GET /sessions — index
    // -------------------------------------------------------------------------

    public function test_index_lists_sessions_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'session:view');
        Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/sessions')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/sessions')
            ->assertStatus(403);
    }

    public function test_index_returns_400_without_org_header(): void
    {
        $this->giveUserPermission($this->user, 'session:view');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/sessions')
            ->assertStatus(400);
    }

    public function test_index_is_scoped_to_current_org(): void
    {
        $this->giveUserPermission($this->user, 'session:view');
        $otherOrg = Org::factory()->create();
        $otherAsset = Asset::factory()->create(['org_id' => $otherOrg->id]);
        Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
        ]);
        Session::factory()->create([
            'org_id' => $otherOrg->id,
            'asset_id' => $otherAsset->id,
            'requester_id' => $this->user->id,
        ]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/sessions')
            ->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertFalse(
            Session::where('org_id', $otherOrg->id)->whereIn('id', $ids)->exists(),
        );
    }

    // -------------------------------------------------------------------------
    // GET /sessions/{id} — show
    // -------------------------------------------------------------------------

    public function test_show_returns_session_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'session:view');
        $session = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions/{$session->id}")
            ->assertStatus(200);
    }

    public function test_show_returns_404_for_nonexistent_session(): void
    {
        $this->giveUserPermission($this->user, 'session:view');

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/sessions/999999')
            ->assertStatus(404);
    }

    public function test_show_returns_404_for_other_orgs_session(): void
    {
        $this->giveUserPermission($this->user, 'session:view');
        $otherOrg = Org::factory()->create();
        $otherAsset = Asset::factory()->create(['org_id' => $otherOrg->id]);
        $session = Session::factory()->create([
            'org_id' => $otherOrg->id,
            'asset_id' => $otherAsset->id,
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions/{$session->id}")
            ->assertStatus(404);
    }

    public function test_show_returns_403_without_permission(): void
    {
        $session = Session::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions/{$session->id}")
            ->assertStatus(403);
    }
}
