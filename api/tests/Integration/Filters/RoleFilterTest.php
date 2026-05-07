<?php

namespace Tests\Integration\Filters;

use App\Models\Org;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleFilterTest extends TestCase
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
        $this->giveUserPermission($this->user, 'role:view');
    }

    public function test_filter_by_name(): void
    {
        Role::factory()->create(['name' => 'Admin Role']);
        Role::factory()->create(['name' => 'Viewer Role']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/roles?name=Admin')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        $this->assertTrue($names->every(fn ($n) => str_contains($n, 'Admin')));
        $this->assertFalse($names->contains('Viewer Role'));
    }

    public function test_sort_by_name(): void
    {
        Role::factory()->create(['name' => 'Zebra Role']);
        Role::factory()->create(['name' => 'Alpha Role']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/roles?sort=name')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        $this->assertLessThan(
            $names->search('Zebra Role'),
            $names->search('Alpha Role')
        );
    }

    public function test_no_filter_returns_all_roles(): void
    {
        Role::factory()->count(3)->create();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/roles')
            ->assertStatus(200);

        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function test_filter_by_description(): void
    {
        Role::factory()->create(['description' => 'Administrator role']);
        Role::factory()->create(['description' => 'Viewer role']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/roles?description=Admin')
            ->assertStatus(200);

        $descriptions = collect($response->json('data'))->pluck('attributes.description');
        $this->assertTrue($descriptions->every(fn ($d) => str_contains($d ?? '', 'Admin')));
    }

    public function test_filter_by_created_at_greater_than(): void
    {
        Role::factory()->create();
        $past = now()->subDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/roles?filter[created_at]={$past}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_updated_at_range(): void
    {
        Role::factory()->create();
        $from = now()->subDay()->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/roles?filter[updated_at]={$from},{$to}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }
}
