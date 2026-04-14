<?php

namespace Tests\Integration\Controllers\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // GET /users — index
    // -------------------------------------------------------------------------

    public function test_index_returns_users_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'user:viewany');
        User::factory()->count(2)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/users');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure(['data', 'meta']);
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/users')
            ->assertStatus(403);
    }

    public function test_index_returns_401_when_unauthenticated(): void
    {
        $this->getJson('/users')->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // POST /users — store
    // -------------------------------------------------------------------------

    public function test_store_creates_user_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'user:create');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'default_timezone' => 'UTC',
            ]);

        $this->assertApiSuccess($response, 201);
        $this->assertDatabaseHas('users', ['email' => 'newuser@example.com']);
    }

    public function test_store_returns_422_for_duplicate_email(): void
    {
        $this->giveUserPermission($this->user, 'user:create');

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/users', [
                'name' => 'Another User',
                'email' => $this->user->email,
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'default_timezone' => 'UTC',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_store_returns_422_when_name_is_missing(): void
    {
        $this->giveUserPermission($this->user, 'user:create');

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/users', [
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'default_timezone' => 'UTC',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_returns_422_when_password_is_too_short(): void
    {
        $this->giveUserPermission($this->user, 'user:create');

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/users', [
                'name' => 'User',
                'email' => 'test@example.com',
                'password' => 'short',
                'password_confirmation' => 'short',
                'default_timezone' => 'UTC',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    public function test_store_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/users', [
                'name' => 'User',
                'email' => 'test@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'default_timezone' => 'UTC',
            ])
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // GET /users/{id} — show
    // -------------------------------------------------------------------------

    public function test_show_returns_any_user_for_viewany_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:viewany');
        $other = User::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/users/{$other->id}");

        $this->assertApiSuccess($response);
    }

    public function test_show_returns_own_user_with_view_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:view');

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/users/{$this->user->id}");

        $this->assertApiSuccess($response);
    }

    public function test_show_returns_403_for_other_user_with_view_only_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:view');
        $other = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/users/{$other->id}")
            ->assertStatus(403);
    }

    public function test_show_returns_403_without_any_permission(): void
    {
        $other = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/users/{$other->id}")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // PUT /users/{id} — update
    // -------------------------------------------------------------------------

    public function test_update_any_user_with_updateany_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:updateany');
        $other = User::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/users/{$other->id}", ['name' => 'Changed']);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('users', ['id' => $other->id, 'name' => 'Changed']);
    }

    public function test_update_own_user_with_update_permission(): void
    {
        $this->giveUserPermission($this->user, 'user:update');

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/users/{$this->user->id}", ['name' => 'Self Updated']);

        $this->assertApiSuccess($response);
    }

    public function test_update_returns_403_without_permission(): void
    {
        $other = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/users/{$other->id}", ['name' => 'Fail'])
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DELETE /users/{id} — destroy
    // -------------------------------------------------------------------------

    public function test_destroy_soft_deletes_user_and_sets_deleted_by(): void
    {
        $this->giveUserPermission($this->user, 'user:deleteany');
        $other = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/users/{$other->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('users', ['id' => $other->id]);
        $this->assertDatabaseHas('users', ['id' => $other->id, 'deleted_by' => $this->user->id]);
    }

    public function test_destroy_returns_400_when_deleting_self(): void
    {
        $this->giveUserPermission($this->user, 'user:deleteany');

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/users/{$this->user->id}")
            ->assertStatus(400);
    }

    public function test_destroy_returns_403_without_permission(): void
    {
        $other = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/users/{$other->id}")
            ->assertStatus(403);
    }
}
