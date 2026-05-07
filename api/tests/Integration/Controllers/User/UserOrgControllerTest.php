<?php

namespace Tests\Integration\Controllers\User;

use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserOrgControllerTest extends TestCase
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
    }

    // -------------------------------------------------------------------------
    // GET /users/me/orgs — index
    // -------------------------------------------------------------------------

    public function test_index_returns_users_orgs(): void
    {
        $this->giveUserPermission($this->user, 'user:view');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/users/me/orgs')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_index_includes_orgs_user_belongs_to(): void
    {
        $this->giveUserPermission($this->user, 'user:view');
        $otherOrg = Org::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/users/me/orgs')
            ->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('attributes.id');
        $this->assertTrue($ids->contains($this->org->id));
        $this->assertFalse($ids->contains($otherOrg->id));
    }

    // -------------------------------------------------------------------------
    // GET /users/me/orgs/{org} — show
    // -------------------------------------------------------------------------

    public function test_show_returns_200_for_org_user_belongs_to(): void
    {
        $this->giveUserPermission($this->user, 'user:view');

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/users/me/orgs/{$this->org->id}")
            ->assertStatus(200);
    }

    public function test_show_returns_404_for_org_user_does_not_belong_to(): void
    {
        $this->giveUserPermission($this->user, 'user:view');
        $otherOrg = Org::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/users/me/orgs/{$otherOrg->id}")
            ->assertStatus(404);
    }
}
