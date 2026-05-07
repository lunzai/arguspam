<?php

namespace Tests\Integration\Filters;

use App\Enums\DatabaseScope;
use App\Enums\RequestStatus;
use App\Models\Asset;
use App\Models\Org;
use App\Models\Request;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RequestFilterTest extends TestCase
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
        $this->giveUserPermission($this->user, 'request:view');
        $this->asset = Asset::factory()->create(['org_id' => $this->org->id]);
    }

    private function makeRequest(array $overrides = []): Request
    {
        return Request::factory()->create(array_merge([
            'org_id' => $this->org->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
        ], $overrides));
    }

    public function test_filter_by_status(): void
    {
        $this->makeRequest(['status' => RequestStatus::SUBMITTED]);
        $this->makeRequest(['status' => RequestStatus::APPROVED]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests?status=submitted')
            ->assertStatus(200);

        $statuses = collect($response->json('data'))->pluck('attributes.status');
        $this->assertTrue($statuses->every(fn ($s) => $s === RequestStatus::SUBMITTED->value));
    }

    public function test_filter_by_requester_id(): void
    {
        $otherUser = User::factory()->create();
        $this->makeRequest(['requester_id' => $this->user->id]);
        $this->makeRequest(['requester_id' => $otherUser->id]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/requests?filter[requester_id]={$this->user->id}")
            ->assertStatus(200);

        $requesterIds = collect($response->json('data'))->pluck('attributes.requester_id');
        $this->assertTrue($requesterIds->every(fn ($id) => $id == $this->user->id));
    }

    public function test_no_filter_returns_all_org_requests(): void
    {
        $this->makeRequest();
        $this->makeRequest();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests')
            ->assertStatus(200);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_filter_by_reason_partial_match(): void
    {
        $this->makeRequest(['reason' => 'Quarterly audit access']);
        $this->makeRequest(['reason' => 'Routine maintenance']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests?reason=audit')
            ->assertStatus(200);

        $reasons = collect($response->json('data'))->pluck('attributes.reason');
        $this->assertTrue($reasons->every(fn ($r) => str_contains(strtolower($r), 'audit')));
        $this->assertFalse($reasons->contains('Routine maintenance'));
    }

    public function test_filter_by_asset_id(): void
    {
        $otherAsset = Asset::factory()->create(['org_id' => $this->org->id]);
        $this->makeRequest(['asset_id' => $this->asset->id]);
        $this->makeRequest(['asset_id' => $otherAsset->id]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/requests?filter[asset_id]={$this->asset->id}")
            ->assertStatus(200);

        $assetIds = collect($response->json('data'))->pluck('attributes.asset_id');
        $this->assertTrue($assetIds->every(fn ($id) => $id == $this->asset->id));
    }

    public function test_filter_by_multiple_statuses(): void
    {
        $this->makeRequest(['status' => RequestStatus::SUBMITTED]);
        $this->makeRequest(['status' => RequestStatus::APPROVED]);
        $this->makeRequest(['status' => RequestStatus::REJECTED]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests?filter[status]=submitted,approved')
            ->assertStatus(200);

        $statuses = collect($response->json('data'))->pluck('attributes.status');
        $this->assertCount(2, $statuses);
        $this->assertTrue($statuses->contains(RequestStatus::SUBMITTED->value));
        $this->assertTrue($statuses->contains(RequestStatus::APPROVED->value));
        $this->assertFalse($statuses->contains(RequestStatus::REJECTED->value));
    }

    public function test_sort_by_status_desc(): void
    {
        $this->makeRequest(['status' => RequestStatus::SUBMITTED]);
        $this->makeRequest(['status' => RequestStatus::APPROVED]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests?sort=-status')
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_scope(): void
    {
        $this->makeRequest(['scope' => DatabaseScope::READ_ONLY]);
        $this->makeRequest(['scope' => DatabaseScope::READ_WRITE]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests?filter[scope]=read_only')
            ->assertStatus(200);

        $scopes = collect($response->json('data'))->pluck('attributes.scope');
        $this->assertTrue($scopes->every(fn ($s) => $s === 'read_only'));
    }

    public function test_filter_by_intended_query_partial_match(): void
    {
        $this->makeRequest(['intended_query' => 'SELECT * FROM users']);
        $this->makeRequest(['intended_query' => 'DELETE FROM logs']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests?filter[intended_query]=SELECT')
            ->assertStatus(200);

        $queries = collect($response->json('data'))->pluck('attributes.intended_query');
        $this->assertTrue($queries->every(fn ($q) => str_contains(strtoupper($q), 'SELECT')));
    }

    public function test_filter_by_org_id(): void
    {
        $this->makeRequest();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/requests?filter[org_id]={$this->org->id}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_created_at_greater_than(): void
    {
        $this->makeRequest();
        $past = now()->subDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/requests?filter[created_at]={$past}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_start_datetime_range(): void
    {
        $this->makeRequest();
        $from = now()->subDay()->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/requests?filter[start_datetime]={$from},{$to}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_is_access_sensitive_data(): void
    {
        $this->makeRequest(['is_access_sensitive_data' => false]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests?filter[is_access_sensitive_data]=0')
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_approver_note_partial_match(): void
    {
        $this->makeRequest(['approver_note' => 'Approved for audit']);
        $this->makeRequest(['approver_note' => null]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests?filter[approver_note]=Approved')
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_approved_at_range(): void
    {
        $this->makeRequest();
        $from = now()->subDay()->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/requests?filter[approved_at]={$from},{$to}")
            ->assertStatus(200);

        $this->assertIsArray($response->json('data'));
    }

    public function test_filter_by_duration(): void
    {
        $this->makeRequest();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests?filter[duration]=0')
            ->assertStatus(200);

        $this->assertIsArray($response->json('data'));
    }

    public function test_filter_by_sensitive_data_note(): void
    {
        $this->makeRequest(['sensitive_data_note' => 'Contains PII data']);
        $this->makeRequest(['sensitive_data_note' => null]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests?filter[sensitive_data_note]=PII')
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_approver_risk_rating(): void
    {
        $this->makeRequest(['approver_risk_rating' => 'low']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/requests?filter[approver_risk_rating]=low')
            ->assertStatus(200);

        $this->assertIsArray($response->json('data'));
    }

    public function test_filter_by_rejected_at(): void
    {
        $this->makeRequest();
        $past = now()->subDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/requests?filter[rejected_at]={$past}")
            ->assertStatus(200);

        $this->assertIsArray($response->json('data'));
    }

    public function test_filter_by_updated_at_range(): void
    {
        $this->makeRequest();
        $from = now()->subDay()->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/requests?filter[updated_at]={$from},{$to}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }
}
