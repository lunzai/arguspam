<?php

namespace Tests\Integration\Filters;

use App\Enums\Status;
use App\Models\Asset;
use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssetFilterTest extends TestCase
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
        $this->giveUserPermission($this->user, 'asset:view');
    }

    public function test_filter_by_name(): void
    {
        Asset::factory()->create(['org_id' => $this->org->id, 'name' => 'Alpha Server']);
        Asset::factory()->create(['org_id' => $this->org->id, 'name' => 'Beta Server']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/assets?name=Alpha')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        $this->assertTrue($names->contains('Alpha Server'));
        $this->assertFalse($names->contains('Beta Server'));
    }

    public function test_filter_by_status(): void
    {
        Asset::factory()->create(['org_id' => $this->org->id, 'status' => Status::ACTIVE->value]);
        Asset::factory()->create(['org_id' => $this->org->id, 'status' => Status::INACTIVE->value]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/assets?status=active')
            ->assertStatus(200);

        $statuses = collect($response->json('data'))->pluck('attributes.status');
        $this->assertTrue($statuses->every(fn ($s) => $s === 'active'));
    }

    public function test_no_filter_returns_all_org_assets(): void
    {
        Asset::factory()->count(3)->create(['org_id' => $this->org->id]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/assets')
            ->assertStatus(200);

        $this->assertCount(3, $response->json('data'));
    }

    public function test_filter_by_host_partial_match(): void
    {
        Asset::factory()->create(['org_id' => $this->org->id, 'host' => '192.168.1.10']);
        Asset::factory()->create(['org_id' => $this->org->id, 'host' => '10.0.0.5']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/assets?host=192.168')
            ->assertStatus(200);

        $hosts = collect($response->json('data'))->pluck('attributes.host');
        $this->assertTrue($hosts->every(fn ($h) => str_contains($h, '192.168')));
        $this->assertFalse($hosts->contains('10.0.0.5'));
    }

    public function test_filter_by_multiple_statuses(): void
    {
        Asset::factory()->create(['org_id' => $this->org->id, 'status' => Status::ACTIVE->value]);
        Asset::factory()->create(['org_id' => $this->org->id, 'status' => Status::INACTIVE->value]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/assets?filter[status]=active,inactive')
            ->assertStatus(200);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_sort_by_name(): void
    {
        Asset::factory()->create(['org_id' => $this->org->id, 'name' => 'Zebra Server']);
        Asset::factory()->create(['org_id' => $this->org->id, 'name' => 'Alpha Server']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/assets?sort=name')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        $this->assertEquals('Alpha Server', $names->first());
    }

    public function test_sort_by_name_desc(): void
    {
        Asset::factory()->create(['org_id' => $this->org->id, 'name' => 'Zebra Server']);
        Asset::factory()->create(['org_id' => $this->org->id, 'name' => 'Alpha Server']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/assets?sort=-name')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        $this->assertEquals('Zebra Server', $names->first());
    }

    public function test_filter_by_created_at_range(): void
    {
        Asset::factory()->create(['org_id' => $this->org->id]);

        $from = now()->subDay()->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/assets?filter[created_at]={$from},{$to}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_org_id(): void
    {
        Asset::factory()->create(['org_id' => $this->org->id]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/assets?filter[org_id]={$this->org->id}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_description(): void
    {
        Asset::factory()->create(['org_id' => $this->org->id, 'description' => 'Production database']);
        Asset::factory()->create(['org_id' => $this->org->id, 'description' => 'Dev environment']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/assets?description=Production')
            ->assertStatus(200);

        $descriptions = collect($response->json('data'))->pluck('attributes.description');
        $this->assertTrue($descriptions->every(fn ($d) => str_contains($d ?? '', 'Production')));
    }

    public function test_filter_by_port(): void
    {
        Asset::factory()->create(['org_id' => $this->org->id, 'port' => 5432]);
        Asset::factory()->create(['org_id' => $this->org->id, 'port' => 3306]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/assets?port=5432')
            ->assertStatus(200);

        $ports = collect($response->json('data'))->pluck('attributes.port');
        $this->assertTrue($ports->every(fn ($p) => $p == 5432));
    }

    public function test_filter_by_dbms(): void
    {
        Asset::factory()->create(['org_id' => $this->org->id, 'dbms' => 'postgresql']);
        Asset::factory()->create(['org_id' => $this->org->id, 'dbms' => 'mysql']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/assets?filter[dbms]=postgresql')
            ->assertStatus(200);

        $this->assertCount(1, $response->json('data'));
    }

    public function test_filter_by_updated_at_range(): void
    {
        Asset::factory()->create(['org_id' => $this->org->id]);
        $from = now()->subDay()->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/assets?filter[updated_at]={$from},{$to}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }
}
