<?php

namespace Tests\Integration\Controllers\Role;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // GET /roles — index
    // -------------------------------------------------------------------------

    public function test_index_returns_roles_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'role:view');
        Role::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/roles');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure(['data', 'meta']);
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/roles')
            ->assertStatus(403);
    }

    public function test_index_returns_401_when_unauthenticated(): void
    {
        $this->getJson('/roles')->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // POST /roles — store
    // -------------------------------------------------------------------------

    public function test_store_creates_role_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'role:create');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/roles', [
                'name' => 'custom-role',
                'description' => 'A test role',
            ]);

        $this->assertApiSuccess($response, 201);
        $this->assertDatabaseHas('roles', ['name' => 'custom-role']);
    }

    public function test_store_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/roles', ['name' => 'some-role'])
            ->assertStatus(403);
    }

    public function test_store_returns_422_when_name_is_missing(): void
    {
        $this->giveUserPermission($this->user, 'role:create');

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/roles', ['description' => 'No name'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    // -------------------------------------------------------------------------
    // GET /roles/{id} — show
    // -------------------------------------------------------------------------

    public function test_show_returns_role_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'role:view');
        $role = Role::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/roles/{$role->id}");

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.attributes.name', (string) $role->name);
    }

    public function test_show_returns_404_for_nonexistent_role(): void
    {
        $this->giveUserPermission($this->user, 'role:view');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/roles/999999')
            ->assertStatus(404);
    }

    public function test_show_returns_403_without_permission(): void
    {
        $role = Role::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/roles/{$role->id}")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // PUT /roles/{id} — update
    // -------------------------------------------------------------------------

    public function test_update_modifies_role_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'role:update');
        $role = Role::factory()->create(['is_default' => false]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/roles/{$role->id}", ['name' => 'updated-role']);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('roles', ['id' => $role->id, 'name' => 'updated-role']);
    }

    public function test_update_returns_422_for_default_role(): void
    {
        $this->giveUserPermission($this->user, 'role:update');
        $role = Role::factory()->create(['is_default' => true]);

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/roles/{$role->id}", ['name' => 'cannot-change'])
            ->assertStatus(422);
    }

    public function test_update_returns_403_without_permission(): void
    {
        $role = Role::factory()->create(['is_default' => false]);

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/roles/{$role->id}", ['name' => 'updated'])
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DELETE /roles/{id} — destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_role_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'role:delete');
        $role = Role::factory()->create(['is_default' => false]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/roles/{$role->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    public function test_destroy_returns_422_for_default_role(): void
    {
        $this->giveUserPermission($this->user, 'role:delete');
        $role = Role::factory()->create(['is_default' => true]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/roles/{$role->id}")
            ->assertStatus(422);
    }

    public function test_destroy_returns_422_when_role_has_assigned_users(): void
    {
        $this->giveUserPermission($this->user, 'role:delete');
        $role = Role::factory()->create(['is_default' => false]);
        $role->users()->attach($this->user->id);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/roles/{$role->id}")
            ->assertStatus(422);
    }

    public function test_destroy_returns_403_without_permission(): void
    {
        $role = Role::factory()->create(['is_default' => false]);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/roles/{$role->id}")
            ->assertStatus(403);
    }
}
