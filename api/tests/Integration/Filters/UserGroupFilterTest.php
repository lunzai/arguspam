<?php

namespace Tests\Integration\Filters;

use App\Enums\Status;
use App\Models\Org;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGroupFilterTest extends TestCase
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
        $this->giveUserPermission($this->user, 'usergroup:view');
    }

    public function test_filter_by_name(): void
    {
        UserGroup::factory()->create(['org_id' => $this->org->id, 'name' => 'Dev Team']);
        UserGroup::factory()->create(['org_id' => $this->org->id, 'name' => 'Ops Team']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/user-groups?name=Dev')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        $this->assertTrue($names->every(fn ($n) => str_contains($n, 'Dev')));
    }

    public function test_filter_by_status(): void
    {
        UserGroup::factory()->create(['org_id' => $this->org->id, 'status' => Status::ACTIVE->value]);
        UserGroup::factory()->create(['org_id' => $this->org->id, 'status' => Status::INACTIVE->value]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/user-groups?status=active')
            ->assertStatus(200);

        $statuses = collect($response->json('data'))->pluck('attributes.status');
        $this->assertTrue($statuses->every(fn ($s) => $s === 'active'));
    }

    public function test_sort_by_name(): void
    {
        UserGroup::factory()->create(['org_id' => $this->org->id, 'name' => 'Zeta Group']);
        UserGroup::factory()->create(['org_id' => $this->org->id, 'name' => 'Alpha Group']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/user-groups?sort=name')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        $this->assertEquals('Alpha Group', $names->first());
    }

    public function test_no_filter_returns_all_org_user_groups(): void
    {
        UserGroup::factory()->count(3)->create(['org_id' => $this->org->id]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/user-groups')
            ->assertStatus(200);

        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function test_filter_by_org_id(): void
    {
        UserGroup::factory()->create(['org_id' => $this->org->id]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/user-groups?filter[org_id]={$this->org->id}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_description(): void
    {
        UserGroup::factory()->create(['org_id' => $this->org->id, 'description' => 'Engineering team group']);
        UserGroup::factory()->create(['org_id' => $this->org->id, 'description' => 'Operations team group']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/user-groups?description=Engineer')
            ->assertStatus(200);

        $descriptions = collect($response->json('data'))->pluck('attributes.description');
        $this->assertTrue($descriptions->every(fn ($d) => str_contains(strtolower($d ?? ''), 'engineer')));
    }

    public function test_filter_by_created_at_greater_than(): void
    {
        UserGroup::factory()->create(['org_id' => $this->org->id]);
        $past = now()->subDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/user-groups?filter[created_at]={$past}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_updated_at_range(): void
    {
        UserGroup::factory()->create(['org_id' => $this->org->id]);
        $from = now()->subDay()->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/user-groups?filter[updated_at]={$from},{$to}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }
}
