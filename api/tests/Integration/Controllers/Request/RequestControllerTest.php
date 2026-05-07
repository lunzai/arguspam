<?php

namespace Tests\Integration\Controllers\Request;

use App\Enums\DatabaseScope;
use App\Enums\RequestStatus;
use App\Models\Asset;
use App\Models\Org;
use App\Models\Request as RequestModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RequestControllerTest extends TestCase
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

    private function validRequestPayload(): array
    {
        return [
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'start_datetime' => now()->addMinutes(5)->toDateTimeString(),
            'end_datetime' => now()->addHour()->toDateTimeString(),
            'reason' => 'Need access to investigate a production issue.',
            'scope' => DatabaseScope::READ_ONLY->value,
            'is_access_sensitive_data' => false,
        ];
    }

    // -------------------------------------------------------------------------
    // GET /requests — index
    // -------------------------------------------------------------------------

    public function test_index_lists_requests_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'request:view');
        RequestModel::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests')
            ->assertStatus(403);
    }

    public function test_index_returns_400_without_org_header(): void
    {
        $this->giveUserPermission($this->user, 'request:view');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/requests')
            ->assertStatus(400);
    }

    public function test_index_is_scoped_to_current_org(): void
    {
        $this->giveUserPermission($this->user, 'request:view');
        $otherOrg = Org::factory()->create();
        $otherAsset = Asset::factory()->create(['org_id' => $otherOrg->id]);
        RequestModel::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
        ]);
        RequestModel::factory()->create([
            'org_id' => $otherOrg->id,
            'asset_id' => $otherAsset->id,
            'requester_id' => $this->user->id,
        ]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests')
            ->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertFalse(
            RequestModel::where('org_id', $otherOrg->id)->whereIn('id', $ids)->exists(),
        );
    }

    // -------------------------------------------------------------------------
    // POST /requests — store
    // -------------------------------------------------------------------------

    public function test_store_creates_pending_request(): void
    {
        $this->giveUserPermission($this->user, 'request:create');

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/requests', $this->validRequestPayload())
            ->assertStatus(201);

        $this->assertDatabaseHas('requests', [
            'org_id' => $this->org->id,
            'requester_id' => $this->user->id,
            'status' => RequestStatus::PENDING->value,
        ]);
    }

    public function test_store_sets_requester_from_auth_user(): void
    {
        $this->giveUserPermission($this->user, 'request:create');

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/requests', $this->validRequestPayload())
            ->assertStatus(201);

        $this->assertDatabaseHas('requests', ['requester_id' => $this->user->id]);
    }

    public function test_store_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/requests', $this->validRequestPayload())
            ->assertStatus(403);
    }

    public function test_store_returns_422_when_reason_is_missing(): void
    {
        $this->giveUserPermission($this->user, 'request:create');
        $payload = $this->validRequestPayload();
        unset($payload['reason']);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/requests', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['reason']);
    }

    public function test_store_returns_422_when_end_datetime_is_before_start(): void
    {
        $this->giveUserPermission($this->user, 'request:create');
        $payload = array_merge($this->validRequestPayload(), [
            'start_datetime' => now()->addHour()->toDateTimeString(),
            'end_datetime' => now()->addMinutes(30)->toDateTimeString(),
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/requests', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_datetime']);
    }

    public function test_store_returns_422_when_end_datetime_is_in_the_past(): void
    {
        $this->giveUserPermission($this->user, 'request:create');
        $payload = array_merge($this->validRequestPayload(), [
            'end_datetime' => now()->subHour()->toDateTimeString(),
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/requests', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['end_datetime']);
    }

    public function test_store_returns_422_when_scope_is_invalid(): void
    {
        $this->giveUserPermission($this->user, 'request:create');
        $payload = array_merge($this->validRequestPayload(), ['scope' => 'invalid_scope']);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/requests', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['scope']);
    }

    public function test_store_returns_422_when_sensitive_data_note_is_missing_for_sensitive_request(): void
    {
        $this->giveUserPermission($this->user, 'request:create');
        $payload = array_merge($this->validRequestPayload(), [
            'is_access_sensitive_data' => true,
            'sensitive_data_note' => null,
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/requests', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['sensitive_data_note']);
    }

    // -------------------------------------------------------------------------
    // GET /requests/{id} — show
    // -------------------------------------------------------------------------

    public function test_show_returns_request_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'request:view');
        $request = RequestModel::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/requests/{$request->id}")
            ->assertStatus(200);
    }

    public function test_show_returns_404_for_nonexistent_request(): void
    {
        $this->giveUserPermission($this->user, 'request:view');

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests/999999')
            ->assertStatus(404);
    }

    public function test_show_returns_404_for_other_orgs_request(): void
    {
        $this->giveUserPermission($this->user, 'request:view');
        $otherOrg = Org::factory()->create();
        $otherAsset = Asset::factory()->create(['org_id' => $otherOrg->id]);
        $request = RequestModel::factory()->create([
            'org_id' => $otherOrg->id,
            'asset_id' => $otherAsset->id,
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/requests/{$request->id}")
            ->assertStatus(404);
    }

    public function test_show_returns_403_without_permission(): void
    {
        $request = RequestModel::factory()->create([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
        ]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/requests/{$request->id}")
            ->assertStatus(403);
    }
}
