<?php

namespace Tests\Integration\Controllers\User;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRoleControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $targetUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->targetUser = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // POST /users/{user}/roles — store (sync roles)
    // -------------------------------------------------------------------------

    public function test_store_assigns_roles_to_user(): void
    {
        $this->giveUserPermission($this->user, 'user:updateany');
        $role = Role::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/users/{$this->targetUser->id}/roles", [
                'role_ids' => [$role->id],
            ])
            ->assertStatus(201);

        $this->assertTrue($this->targetUser->roles()->where('roles.id', $role->id)->exists());
    }

    public function test_store_returns_422_for_invalid_role_ids(): void
    {
        $this->giveUserPermission($this->user, 'user:updateany');

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/users/{$this->targetUser->id}/roles", [
                'role_ids' => [999999],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['role_ids.0']);
    }

    public function test_store_returns_403_without_permission(): void
    {
        $role = Role::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/users/{$this->targetUser->id}/roles", [
                'role_ids' => [$role->id],
            ])
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DELETE /users/{user}/roles — destroy (remove roles)
    // -------------------------------------------------------------------------

    public function test_destroy_removes_roles_from_user(): void
    {
        $this->giveUserPermission($this->user, 'user:updateany');
        $role = Role::factory()->create();
        $this->targetUser->roles()->attach($role->id);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/users/{$this->targetUser->id}/roles", [
                'role_ids' => [$role->id],
            ])
            ->assertStatus(204);

        $this->assertFalse($this->targetUser->roles()->where('roles.id', $role->id)->exists());
    }

    public function test_destroy_returns_422_for_invalid_role_ids(): void
    {
        $this->giveUserPermission($this->user, 'user:updateany');

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/users/{$this->targetUser->id}/roles", [
                'role_ids' => [999999],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['role_ids.0']);
    }

    public function test_destroy_returns_403_without_permission(): void
    {
        $role = Role::factory()->create();
        $this->targetUser->roles()->attach($role->id);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/users/{$this->targetUser->id}/roles", [
                'role_ids' => [$role->id],
            ])
            ->assertStatus(403);
    }
}
