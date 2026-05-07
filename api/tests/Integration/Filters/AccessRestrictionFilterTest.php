<?php

namespace Tests\Integration\Filters;

use App\Enums\AccessRestrictionType;
use App\Enums\Status;
use App\Models\AccessRestriction;
use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessRestrictionFilterTest extends TestCase
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
        $this->giveUserPermission($this->user, 'accessrestriction:view');
    }

    private function makeRestriction(array $overrides = []): AccessRestriction
    {
        return AccessRestriction::create(array_merge([
            'name' => 'Test Restriction',
            'description' => 'A test restriction',
            'type' => AccessRestrictionType::IP_ADDRESS->value,
            'data' => ['allowed_ips' => ['127.0.0.1']],
            'status' => Status::ACTIVE->value,
            'weight' => 0,
        ], $overrides));
    }

    public function test_filter_by_name(): void
    {
        $this->makeRestriction(['name' => 'Office IP Restriction']);
        $this->makeRestriction(['name' => 'VPN Restriction']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/access-restrictions?name=Office')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        $this->assertTrue($names->every(fn ($n) => str_contains($n, 'Office')));
    }

    public function test_filter_by_status(): void
    {
        $this->makeRestriction(['status' => Status::ACTIVE->value]);
        $this->makeRestriction(['status' => Status::INACTIVE->value]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/access-restrictions?status=active')
            ->assertStatus(200);

        $statuses = collect($response->json('data'))->pluck('attributes.status');
        $this->assertTrue($statuses->every(fn ($s) => $s === 'active'));
    }

    public function test_filter_by_type(): void
    {
        $this->makeRestriction(['type' => AccessRestrictionType::IP_ADDRESS->value]);
        $this->makeRestriction(['type' => AccessRestrictionType::TIME_WINDOW->value, 'data' => ['days' => [1, 2]]]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/access-restrictions?filter[type]=ip+address')
            ->assertStatus(200);

        $types = collect($response->json('data'))->pluck('attributes.type');
        $this->assertTrue($types->every(fn ($t) => $t === AccessRestrictionType::IP_ADDRESS->value));
    }

    public function test_sort_by_name(): void
    {
        $this->makeRestriction(['name' => 'Zebra Restriction']);
        $this->makeRestriction(['name' => 'Alpha Restriction']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/access-restrictions?sort=name')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        $this->assertEquals('Alpha Restriction', $names->first());
    }

    public function test_no_filter_returns_all(): void
    {
        $this->makeRestriction();
        $this->makeRestriction(['name' => 'Another Restriction']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/access-restrictions')
            ->assertStatus(200);

        $this->assertGreaterThanOrEqual(2, count($response->json('data')));
    }

    public function test_filter_by_description(): void
    {
        $this->makeRestriction(['description' => 'Blocks home network access']);
        $this->makeRestriction(['name' => 'VPN', 'description' => 'Requires VPN connection']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/access-restrictions?description=Blocks')
            ->assertStatus(200);

        $descriptions = collect($response->json('data'))->pluck('attributes.description');
        $this->assertTrue($descriptions->every(fn ($d) => str_contains($d ?? '', 'Blocks')));
    }

    public function test_filter_by_weight_range(): void
    {
        $this->makeRestriction(['weight' => 5]);
        $this->makeRestriction(['name' => 'High Weight', 'weight' => 100]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/access-restrictions?filter[weight]=0,10')
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_created_at_greater_than(): void
    {
        $this->makeRestriction();
        $past = now()->subDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/access-restrictions?filter[created_at]={$past}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_updated_at_range(): void
    {
        $this->makeRestriction();
        $from = now()->subDay()->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/access-restrictions?filter[updated_at]={$from},{$to}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }
}
