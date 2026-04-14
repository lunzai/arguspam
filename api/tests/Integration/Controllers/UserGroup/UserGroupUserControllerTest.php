<?php

namespace Tests\Integration\Controllers\UserGroup;

use App\Models\Org;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserGroupUserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Org $org;
    private UserGroup $group;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
        $this->group = UserGroup::factory()->create(['org_id' => $this->org->id]);
    }

    // -------------------------------------------------------------------------
    // POST /user-groups/{group}/users — store
    // -------------------------------------------------------------------------

    public function test_store_adds_user_in_same_org_to_group(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:adduser');
        $target = User::factory()->create();
        $this->org->users()->attach($target->id);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson("/user-groups/{$this->group->id}/users", [
                'user_ids' => [$target->id],
            ])
            ->assertStatus(201);

        $this->assertTrue($this->group->users()->where('users.id', $target->id)->exists());
    }

    public function test_store_returns_422_for_user_not_in_org(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:adduser');
        $outsider = User::factory()->create();

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson("/user-groups/{$this->group->id}/users", [
                'user_ids' => [$outsider->id],
            ])
            ->assertStatus(422);
    }

    public function test_store_returns_422_for_missing_user_ids(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:adduser');

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson("/user-groups/{$this->group->id}/users", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_ids']);
    }

    public function test_store_returns_403_without_permission(): void
    {
        $target = User::factory()->create();
        $this->org->users()->attach($target->id);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson("/user-groups/{$this->group->id}/users", [
                'user_ids' => [$target->id],
            ])
            ->assertStatus(403);
    }

    public function test_store_is_idempotent(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:adduser');
        $target = User::factory()->create();
        $this->org->users()->attach($target->id);
        $this->group->users()->attach($target->id);

        $this->actingAsWithOrg($this->user, $this->org)
            ->postJson("/user-groups/{$this->group->id}/users", [
                'user_ids' => [$target->id],
            ])
            ->assertStatus(201);

        $this->assertEquals(1, $this->group->users()->where('users.id', $target->id)->count());
    }

    // -------------------------------------------------------------------------
    // DELETE /user-groups/{group}/users — destroy
    // -------------------------------------------------------------------------

    public function test_destroy_removes_user_from_group(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:removeuser');
        $target = User::factory()->create();
        $this->org->users()->attach($target->id);
        $this->group->users()->attach($target->id);

        $this->actingAsWithOrg($this->user, $this->org)
            ->deleteJson("/user-groups/{$this->group->id}/users", [
                'user_ids' => [$target->id],
            ])
            ->assertStatus(204);

        $this->assertFalse($this->group->users()->where('users.id', $target->id)->exists());
    }

    public function test_destroy_returns_422_for_missing_user_ids(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:removeuser');

        $this->actingAsWithOrg($this->user, $this->org)
            ->deleteJson("/user-groups/{$this->group->id}/users", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_ids']);
    }

    public function test_destroy_returns_403_without_permission(): void
    {
        $target = User::factory()->create();
        $this->group->users()->attach($target->id);

        $this->actingAsWithOrg($this->user, $this->org)
            ->deleteJson("/user-groups/{$this->group->id}/users", [
                'user_ids' => [$target->id],
            ])
            ->assertStatus(403);
    }
}
