<?php

namespace Tests\Integration\Controllers\UserGroup;

use App\Enums\Status;
use App\Models\Org;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGroupControllerTest extends TestCase
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

    private function validPayload(): array
    {
        return [
            'org_id' => $this->org->id,
            'name' => 'Test Group',
            'status' => Status::ACTIVE->value,
        ];
    }

    // -------------------------------------------------------------------------
    // GET /user-groups — index
    // -------------------------------------------------------------------------

    public function test_index_lists_user_groups_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:view');
        UserGroup::factory()->create(['org_id' => $this->org->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/user-groups')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/user-groups')
            ->assertStatus(403);
    }

    public function test_index_returns_400_without_org_header(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:view');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/user-groups')
            ->assertStatus(400);
    }

    public function test_index_is_scoped_to_current_org(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:view');
        $otherOrg = Org::factory()->create();
        UserGroup::factory()->create(['org_id' => $this->org->id]);
        UserGroup::factory()->create(['org_id' => $otherOrg->id]);

        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/user-groups')
            ->assertStatus(200);

        $ids = collect($response->json('data'))->pluck('id');
        $this->assertFalse(
            UserGroup::where('org_id', $otherOrg->id)->whereIn('id', $ids)->exists()
        );
    }

    // -------------------------------------------------------------------------
    // POST /user-groups — store
    // -------------------------------------------------------------------------

    public function test_store_creates_user_group(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:create');

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/user-groups', $this->validPayload())
            ->assertStatus(201);

        $this->assertDatabaseHas('user_groups', [
            'org_id' => $this->org->id,
            'name' => 'Test Group',
        ]);
    }

    public function test_store_returns_422_for_missing_name(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:create');

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/user-groups', [
                'org_id' => $this->org->id,
                'status' => Status::ACTIVE->value,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_returns_403_without_permission(): void
    {
        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson('/user-groups', $this->validPayload())
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // GET /user-groups/{id} — show
    // -------------------------------------------------------------------------

    public function test_show_returns_user_group_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:view');
        $group = UserGroup::factory()->create(['org_id' => $this->org->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/user-groups/{$group->id}")
            ->assertStatus(200);
    }

    public function test_show_returns_403_without_permission(): void
    {
        $group = UserGroup::factory()->create(['org_id' => $this->org->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson("/user-groups/{$group->id}")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // PUT /user-groups/{id} — update
    // -------------------------------------------------------------------------

    public function test_update_modifies_user_group(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:update');
        $group = UserGroup::factory()->create(['org_id' => $this->org->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->putJson("/user-groups/{$group->id}", [
                'name' => 'Updated Name',
                'status' => Status::ACTIVE->value,
            ])
            ->assertStatus(200);

        $this->assertEquals('Updated Name', $group->fresh()->name);
    }

    public function test_update_returns_403_without_permission(): void
    {
        $group = UserGroup::factory()->create(['org_id' => $this->org->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->putJson("/user-groups/{$group->id}", ['name' => 'Updated Name'])
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DELETE /user-groups/{id} — destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_empty_user_group(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:delete');
        $group = UserGroup::factory()->create(['org_id' => $this->org->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->deleteJson("/user-groups/{$group->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted($group);
    }

    public function test_destroy_returns_400_for_non_empty_group(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:delete');
        $group = UserGroup::factory()->create(['org_id' => $this->org->id]);
        $group->users()->attach($this->user->id);

        $this->actingAsWithOrg($this->user, $this->org)
            ->deleteJson("/user-groups/{$group->id}")
            ->assertStatus(400);
    }

    public function test_destroy_returns_403_without_permission(): void
    {
        $group = UserGroup::factory()->create(['org_id' => $this->org->id]);

        $this->actingAsWithOrg($this->user, $this->org)
            ->deleteJson("/user-groups/{$group->id}")
            ->assertStatus(403);
    }
}
