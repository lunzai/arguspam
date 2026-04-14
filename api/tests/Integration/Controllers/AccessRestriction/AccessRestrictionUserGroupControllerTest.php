<?php

namespace Tests\Integration\Controllers\AccessRestriction;

use App\Enums\AccessRestrictionType;
use App\Enums\Status;
use App\Models\AccessRestriction;
use App\Models\Org;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessRestrictionUserGroupControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private AccessRestriction $restriction;
    private UserGroup $group;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->restriction = AccessRestriction::create([
            'name' => 'Test Restriction',
            'type' => AccessRestrictionType::IP_ADDRESS->value,
            'data' => ['allow' => ['10.0.0.1']],
            'status' => Status::ACTIVE->value,
            'weight' => 1,
        ]);
        $org = Org::factory()->create();
        $this->group = UserGroup::factory()->create(['org_id' => $org->id]);
    }

    // -------------------------------------------------------------------------
    // POST /access-restrictions/{id}/user-groups — addUserGroup
    // -------------------------------------------------------------------------

    public function test_store_adds_user_group_to_restriction(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:addusergroup');

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/access-restrictions/{$this->restriction->id}/user-groups", [
                'user_group_ids' => [$this->group->id],
            ])
            ->assertStatus(201);

        $this->assertTrue($this->restriction->userGroups()->where('user_groups.id', $this->group->id)->exists());
    }

    public function test_store_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/access-restrictions/{$this->restriction->id}/user-groups", [
                'user_group_ids' => [$this->group->id],
            ])
            ->assertStatus(403);
    }

    public function test_store_returns_422_for_missing_user_group_ids(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:addusergroup');

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/access-restrictions/{$this->restriction->id}/user-groups", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_group_ids']);
    }

    // -------------------------------------------------------------------------
    // DELETE /access-restrictions/{id}/user-groups — removeUserGroup
    // -------------------------------------------------------------------------

    public function test_destroy_removes_user_group_from_restriction(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:removeusergroup');
        $this->restriction->userGroups()->attach($this->group->id);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/access-restrictions/{$this->restriction->id}/user-groups", [
                'user_group_ids' => [$this->group->id],
            ])
            ->assertStatus(204);

        $this->assertFalse($this->restriction->userGroups()->where('user_groups.id', $this->group->id)->exists());
    }

    public function test_destroy_returns_403_without_permission(): void
    {
        $this->restriction->userGroups()->attach($this->group->id);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/access-restrictions/{$this->restriction->id}/user-groups", [
                'user_group_ids' => [$this->group->id],
            ])
            ->assertStatus(403);
    }
}
