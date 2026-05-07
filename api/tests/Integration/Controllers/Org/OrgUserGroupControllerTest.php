<?php

namespace Tests\Integration\Controllers\Org;

use App\Models\Org;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrgUserGroupControllerTest extends TestCase
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
    // GET /orgs/{org}/user-groups — index
    // -------------------------------------------------------------------------

    public function test_index_lists_user_groups_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'org:listusergroups');
        UserGroup::factory()->create(['org_id' => $this->org->id]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/orgs/{$this->org->id}/user-groups")
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson("/orgs/{$this->org->id}/user-groups")
            ->assertStatus(403);
    }

    public function test_index_is_scoped_to_the_given_org(): void
    {
        $this->giveUserPermission($this->user, 'org:listusergroups');
        $otherOrg = Org::factory()->create();
        UserGroup::factory()->create(['org_id' => $this->org->id]);
        $otherGroup = UserGroup::factory()->create(['org_id' => $otherOrg->id]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/orgs/{$this->org->id}/user-groups")
            ->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('attributes.id');
        $this->assertFalse($ids->contains($otherGroup->id));
    }
}
