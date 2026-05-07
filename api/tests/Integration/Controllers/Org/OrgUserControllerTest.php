<?php

namespace Tests\Integration\Controllers\Org;

use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrgUserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->org = Org::factory()->create();
        $this->org->users()->attach($this->user->id);
    }

    // -------------------------------------------------------------------------
    // GET /orgs/{org}/users — index
    // -------------------------------------------------------------------------

    public function test_index_lists_users_in_org_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'org:listusers');
        $extra = User::factory()->count(2)->create();
        $this->org->users()->attach($extra->pluck('id')->toArray());

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/orgs/{$this->org->id}/users");

        $this->assertApiSuccess($response);
        $response->assertJsonStructure(['data', 'meta']);
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson("/orgs/{$this->org->id}/users")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // POST /orgs/{org}/users — store (add users)
    // -------------------------------------------------------------------------

    public function test_store_adds_users_to_org(): void
    {
        $this->giveUserPermission($this->user, 'org:adduser');
        $newUser = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/orgs/{$this->org->id}/users", [
                'user_ids' => [$newUser->id],
            ])
            ->assertStatus(201);

        $this->assertTrue($this->org->users()->where('users.id', $newUser->id)->exists());
    }

    public function test_store_is_idempotent_when_user_already_in_org(): void
    {
        $this->giveUserPermission($this->user, 'org:adduser');

        // user is already attached in setUp
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/orgs/{$this->org->id}/users", [
                'user_ids' => [$this->user->id],
            ])
            ->assertStatus(201);

        $this->assertEquals(
            1,
            $this->org->users()->where('users.id', $this->user->id)->count()
        );
    }

    public function test_store_returns_422_for_invalid_user_ids(): void
    {
        $this->giveUserPermission($this->user, 'org:adduser');

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/orgs/{$this->org->id}/users", [
                'user_ids' => [999999],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_ids.0']);
    }

    public function test_store_returns_422_when_user_ids_is_missing(): void
    {
        $this->giveUserPermission($this->user, 'org:adduser');

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/orgs/{$this->org->id}/users", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_ids']);
    }

    public function test_store_returns_403_without_permission(): void
    {
        $newUser = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/orgs/{$this->org->id}/users", [
                'user_ids' => [$newUser->id],
            ])
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DELETE /orgs/{org}/users — destroy (remove users)
    // -------------------------------------------------------------------------

    public function test_destroy_removes_users_from_org(): void
    {
        $this->giveUserPermission($this->user, 'org:removeuser');
        $targetUser = User::factory()->create();
        $this->org->users()->attach($targetUser->id);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/orgs/{$this->org->id}/users", [
                'user_ids' => [$targetUser->id],
            ])
            ->assertStatus(204);

        $this->assertFalse($this->org->users()->where('users.id', $targetUser->id)->exists());
    }

    public function test_destroy_returns_422_for_invalid_user_ids(): void
    {
        $this->giveUserPermission($this->user, 'org:removeuser');

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/orgs/{$this->org->id}/users", [
                'user_ids' => [999999],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_ids.0']);
    }

    public function test_destroy_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/orgs/{$this->org->id}/users", [
                'user_ids' => [$this->user->id],
            ])
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Membership and org-scoped route access
    // -------------------------------------------------------------------------

    public function test_user_added_to_org_can_access_org_scoped_routes(): void
    {
        $this->giveUserPermission($this->user, 'org:adduser');
        $newUser = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/orgs/{$this->org->id}/users", [
                'user_ids' => [$newUser->id],
            ])
            ->assertStatus(201);

        // newUser can now hit org-scoped route
        $this->giveUserPermission($newUser, 'asset:view');
        $this->actingAsWithOrg($newUser, $this->org)
            ->getJson('/assets')
            ->assertStatus(200);
    }

    public function test_user_removed_from_org_is_blocked_on_org_scoped_routes(): void
    {
        $this->giveUserPermission($this->user, 'org:removeuser');
        $targetUser = User::factory()->create();
        $this->org->users()->attach($targetUser->id);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/orgs/{$this->org->id}/users", [
                'user_ids' => [$targetUser->id],
            ])
            ->assertStatus(204);

        // targetUser can no longer access org-scoped routes
        $this->actingAsWithOrg($targetUser, $this->org)
            ->getJson('/assets')
            ->assertStatus(403);
    }
}
