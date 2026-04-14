<?php

namespace Tests\Integration\Filters;

use App\Models\Org;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionFilterTest extends TestCase
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
        $this->giveUserPermission($this->user, 'permission:view');
    }

    public function test_filter_by_name(): void
    {
        Permission::factory()->create(['name' => 'asset:view']);
        Permission::factory()->create(['name' => 'user:create']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/permissions?name=asset')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        $this->assertTrue($names->every(fn ($n) => str_contains($n, 'asset')));
    }

    public function test_sort_by_name(): void
    {
        Permission::factory()->create(['name' => 'zzz:permission']);
        Permission::factory()->create(['name' => 'aaa:permission']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/permissions?sort=name')
            ->assertStatus(200);

        $names = collect($response->json('data'))->pluck('attributes.name');
        $this->assertTrue($names->first() <= $names->last());
    }

    public function test_no_filter_returns_all_permissions(): void
    {
        Permission::factory()->count(3)->create();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/permissions')
            ->assertStatus(200);

        $this->assertGreaterThanOrEqual(3, count($response->json('data')));
    }

    public function test_filter_by_description(): void
    {
        Permission::factory()->create(['description' => 'View assets permission']);
        Permission::factory()->create(['description' => 'Create users permission']);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/permissions?description=View')
            ->assertStatus(200);

        $descriptions = collect($response->json('data'))->pluck('attributes.description');
        $this->assertTrue($descriptions->every(fn ($d) => str_contains($d ?? '', 'View')));
    }

    public function test_filter_by_created_at_greater_than(): void
    {
        Permission::factory()->create();
        $past = now()->subDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/permissions?filter[created_at]={$past}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }

    public function test_filter_by_updated_at_range(): void
    {
        Permission::factory()->create();
        $from = now()->subDay()->toDateTimeString();
        $to = now()->addDay()->toDateTimeString();

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/permissions?filter[updated_at]={$from},{$to}")
            ->assertStatus(200);

        $this->assertNotEmpty($response->json('data'));
    }
}
