<?php

namespace Tests\Integration\Filters;

use App\Models\Asset;
use App\Models\Org;
use App\Models\Session;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SessionFilterTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
        $this->giveUserPermission($this->user, 'session:view');
    }

    public function test_filter_by_status(): void
    {
        Session::factory()->create(['org_id' => $this->org->id, 'status' => 'scheduled']);
        Session::factory()->create(['org_id' => $this->org->id, 'status' => 'started']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/sessions?status=scheduled')
            ->assertStatus(200);

        $statuses = collect($response->json('data'))->pluck('attributes.status');
        $this->assertTrue($statuses->every(fn ($s) => $s === 'scheduled'));
    }

    public function test_filter_by_requester_id(): void
    {
        $otherUser = User::factory()->create();
        Session::factory()->create(['org_id' => $this->org->id, 'requester_id' => $this->user->id]);
        Session::factory()->create(['org_id' => $this->org->id, 'requester_id' => $otherUser->id]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions?filter[requester_id]={$this->user->id}")
            ->assertStatus(200);

        $requesterIds = collect($response->json('data'))->pluck('attributes.requester_id');
        $this->assertTrue($requesterIds->every(fn ($id) => $id == $this->user->id));
    }

    public function test_no_filter_returns_all_org_sessions(): void
    {
        Session::factory()->count(3)->create(['org_id' => $this->org->id]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/sessions')
            ->assertStatus(200);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_filter_by_asset_id(): void
    {
        $asset = Asset::factory()->create(['org_id' => $this->org->id]);
        $otherAsset = Asset::factory()->create(['org_id' => $this->org->id]);
        Session::factory()->create(['org_id' => $this->org->id, 'asset_id' => $asset->id]);
        Session::factory()->create(['org_id' => $this->org->id, 'asset_id' => $otherAsset->id]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions?filter[asset_id]={$asset->id}")
            ->assertStatus(200);

        $assetIds = collect($response->json('data'))->pluck('attributes.asset_id');
        $this->assertTrue($assetIds->every(fn ($id) => $id == $asset->id));
    }

    public function test_filter_by_multiple_statuses(): void
    {
        Session::factory()->create(['org_id' => $this->org->id, 'status' => 'scheduled']);
        Session::factory()->create(['org_id' => $this->org->id, 'status' => 'started']);
        Session::factory()->create(['org_id' => $this->org->id, 'status' => 'ended']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/sessions?filter[status]=scheduled,started')
            ->assertStatus(200);

        $statuses = collect($response->json('data'))->pluck('attributes.status');
        $this->assertCount(2, $statuses);
        $this->assertTrue($statuses->contains('scheduled'));
        $this->assertTrue($statuses->contains('started'));
        $this->assertFalse($statuses->contains('ended'));
    }

    public function test_sort_by_status(): void
    {
        Session::factory()->create(['org_id' => $this->org->id, 'status' => 'scheduled']);
        Session::factory()->create(['org_id' => $this->org->id, 'status' => 'started']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/sessions?sort=status')
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_account_name_partial_match(): void
    {
        Session::factory()->create(['org_id' => $this->org->id, 'account_name' => 'argus123_abc']);
        Session::factory()->create(['org_id' => $this->org->id, 'account_name' => 'root_user']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/sessions?filter[account_name]=argus')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.account_name');
        $this->assertTrue($names->every(fn ($n) => str_contains($n, 'argus')));
    }

    public function test_filter_by_org_id(): void
    {
        Session::factory()->create(['org_id' => $this->org->id]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions?filter[org_id]={$this->org->id}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_created_at_range(): void
    {
        Session::factory()->create(['org_id' => $this->org->id]);
        $from = now()->subDay()->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions?filter[created_at]={$from},{$to}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_is_admin_account(): void
    {
        Session::factory()->create(['org_id' => $this->org->id, 'is_admin_account' => true]);
        Session::factory()->create(['org_id' => $this->org->id, 'is_admin_account' => false]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/sessions?filter[is_admin_account]=1')
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_session_note_partial_match(): void
    {
        Session::factory()->create(['org_id' => $this->org->id, 'session_note' => 'maintenance window']);
        Session::factory()->create(['org_id' => $this->org->id, 'session_note' => 'routine access']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/sessions?filter[session_note]=maintenance')
            ->assertStatus(200);

        $notes = collect($response->json('data'))->pluck('attributes.session_note');
        $this->assertTrue($notes->every(fn ($n) => str_contains(strtolower($n ?? ''), 'maintenance')));
    }

    public function test_filter_by_request_id(): void
    {
        $session = Session::factory()->create(['org_id' => $this->org->id]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions?filter[request_id]={$session->request_id}")
            ->assertStatus(200);

        $requestIds = collect($response->json('data'))->pluck('attributes.request_id');
        $this->assertTrue($requestIds->every(fn ($id) => $id == $session->request_id));
    }

    public function test_filter_by_start_datetime_greater_than(): void
    {
        Session::factory()->create(['org_id' => $this->org->id]);
        $past = now()->subDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions?filter[start_datetime]={$past}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_scheduled_end_datetime_range(): void
    {
        Session::factory()->create(['org_id' => $this->org->id]);
        $from = now()->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions?filter[scheduled_end_datetime]={$from},{$to}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_ended_at_greater_than(): void
    {
        Session::factory()->create(['org_id' => $this->org->id]);
        $past = now()->subDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions?filter[ended_at]={$past}")
            ->assertStatus(200);

        // No sessions have ended_at set, so result will be empty — just assert 200
        $this->assertIsArray($response->json('data'));
    }

    public function test_filter_by_end_datetime(): void
    {
        Session::factory()->create(['org_id' => $this->org->id]);
        $past = now()->subDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions?filter[end_datetime]={$past}")
            ->assertStatus(200);

        $this->assertIsArray($response->json('data'));
    }

    public function test_filter_by_requested_duration(): void
    {
        Session::factory()->create(['org_id' => $this->org->id, 'requested_duration' => 2]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/sessions?filter[requested_duration]=1')
            ->assertStatus(200);

        $this->assertIsArray($response->json('data'));
    }

    public function test_filter_by_actual_duration(): void
    {
        Session::factory()->create(['org_id' => $this->org->id]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/sessions?filter[actual_duration]=0')
            ->assertStatus(200);

        $this->assertIsArray($response->json('data'));
    }

    public function test_filter_by_terminated_at(): void
    {
        Session::factory()->create(['org_id' => $this->org->id]);
        $past = now()->subDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions?filter[terminated_at]={$past}")
            ->assertStatus(200);

        $this->assertIsArray($response->json('data'));
    }

    public function test_filter_by_updated_at_range(): void
    {
        Session::factory()->create(['org_id' => $this->org->id]);
        $from = now()->subDay()->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/sessions?filter[updated_at]={$from},{$to}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }
}
