<?php

namespace Tests\Integration\Filters;

use App\Enums\Status;
use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFilterTest extends TestCase
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
        $this->giveUserPermission($this->user, 'user:viewany');
    }

    public function test_filter_by_name(): void
    {
        User::factory()->create(['name' => 'Alice Smith']);
        User::factory()->create(['name' => 'Bob Jones']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/users?name=Alice')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        $this->assertTrue($names->contains('Alice Smith'));
        $this->assertFalse($names->contains('Bob Jones'));
    }

    public function test_filter_by_status(): void
    {
        User::factory()->create(['status' => Status::ACTIVE->value]);
        User::factory()->create(['status' => Status::INACTIVE->value]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/users?status=active')
            ->assertStatus(200);

        $statuses = collect($response->json('data'))->pluck('attributes.status');
        $this->assertTrue($statuses->every(fn ($s) => $s === 'active'));
    }

    public function test_no_filter_returns_all_users(): void
    {
        User::factory()->count(3)->create();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/users')
            ->assertStatus(200);

        // Total users including $this->user
        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function test_filter_by_multiple_statuses(): void
    {
        User::factory()->create(['status' => Status::ACTIVE->value]);
        User::factory()->create(['status' => Status::INACTIVE->value]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/users?filter[status]=active,inactive')
            ->assertStatus(200);

        $statuses = collect($response->json('data'))->pluck('attributes.status');
        $this->assertTrue($statuses->contains(Status::ACTIVE->value));
        $this->assertTrue($statuses->contains(Status::INACTIVE->value));
    }

    public function test_sort_by_name(): void
    {
        User::factory()->create(['name' => 'Zara Young']);
        User::factory()->create(['name' => 'Aaron Brown']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/users?sort=name')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        $this->assertEquals('Aaron Brown', $names->first());
    }

    public function test_include_relations(): void
    {
        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/users?include=orgs')
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_invalid_include_relation_is_ignored(): void
    {
        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/users?include=nonExistentRelation')
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_include_relation_on_show_endpoint(): void
    {
        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/users/{$this->user->id}?include=orgs")
            ->assertStatus(200);

        $this->assertNotNull($response->json('data'));
    }

    public function test_invalid_include_on_show_endpoint_is_ignored(): void
    {
        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/users/{$this->user->id}?include=fakeRelation")
            ->assertStatus(200);

        $this->assertNotNull($response->json('data'));
    }

    public function test_filter_by_email(): void
    {
        User::factory()->create(['email' => 'alice@example.com']);
        User::factory()->create(['email' => 'bob@example.com']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/users?email=alice')
            ->assertStatus(200);

        $emails = collect($response->json('data'))->pluck('attributes.email');
        $this->assertTrue($emails->every(fn ($e) => str_contains(strtolower($e), 'alice')));
    }

    public function test_filter_by_created_at_greater_than(): void
    {
        User::factory()->create();
        $past = now()->subDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/users?filter[created_at]={$past}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_updated_at_range(): void
    {
        User::factory()->create();
        $from = now()->subDay()->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/users?filter[updated_at]={$from},{$to}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_last_login_at(): void
    {
        User::factory()->create();
        $past = now()->subDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/users?filter[last_login_at]={$past}")
            ->assertStatus(200);

        $this->assertIsArray($response->json('data'));
    }

    public function test_filter_by_two_factor_enabled(): void
    {
        User::factory()->create(['two_factor_enabled' => false]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/users?filter[two_factor_enabled]=0')
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_sort_by_invalid_field_is_ignored(): void
    {
        // Exercises QueryFilter::sort() continue branch (unknown sort field is skipped)
        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/users?sort=invalid_sort_field')
            ->assertStatus(200);

        $this->assertIsArray($response->json('data'));
    }

    public function test_filter_by_created_at_less_than(): void
    {
        // Exercises QueryFilter::filterTimestamp() negative-prefix branch
        User::factory()->create();
        $future = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/users?filter[created_at]=-{$future}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_count_with_relation(): void
    {
        // Exercises QueryFilter::count() method
        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/users?count=roles')
            ->assertStatus(200);

        $this->assertIsArray($response->json('data'));
    }
}
