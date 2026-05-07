<?php

namespace Tests\Integration\Filters;

use App\Enums\Status;
use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrgFilterTest extends TestCase
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
        $this->giveUserPermission($this->user, 'org:view');
    }

    public function test_filter_by_name(): void
    {
        Org::factory()->create(['name' => 'Acme Corp']);
        Org::factory()->create(['name' => 'Beta Inc']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/orgs?name=Acme')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        $this->assertTrue($names->every(fn ($n) => str_contains(strtolower($n), 'acme')));
        $this->assertFalse($names->contains('Beta Inc'));
    }

    public function test_filter_by_status(): void
    {
        Org::factory()->create(['status' => Status::ACTIVE->value]);
        Org::factory()->create(['status' => Status::INACTIVE->value]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/orgs?status=active')
            ->assertStatus(200);

        $statuses = collect($response->json('data'))->pluck('attributes.status');
        $this->assertTrue($statuses->every(fn ($s) => $s === 'active'));
    }

    public function test_sort_by_name(): void
    {
        Org::factory()->create(['name' => 'Zeta Org']);
        Org::factory()->create(['name' => 'Alpha Org']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/orgs?sort=name')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        // Verify Alpha Org comes before Zeta Org (sort ascending works)
        $this->assertLessThan(
            $names->search('Zeta Org'),
            $names->search('Alpha Org')
        );
    }

    public function test_no_filter_returns_all_orgs(): void
    {
        Org::factory()->count(2)->create();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/orgs')
            ->assertStatus(200);

        // Includes $this->org + 2 factory orgs
        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    public function test_filter_by_description(): void
    {
        Org::factory()->create(['description' => 'Tech company']);
        Org::factory()->create(['description' => 'Finance firm']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/orgs?description=Tech')
            ->assertStatus(200);

        $descriptions = collect($response->json('data'))->pluck('attributes.description');
        $this->assertTrue($descriptions->every(fn ($d) => str_contains($d, 'Tech')));
    }

    public function test_filter_by_created_at_greater_than(): void
    {
        Org::factory()->create();
        $past = now()->subDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/orgs?filter[created_at]={$past}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_updated_at_range(): void
    {
        Org::factory()->create();
        $from = now()->subDay()->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/orgs?filter[updated_at]={$from},{$to}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }
}
