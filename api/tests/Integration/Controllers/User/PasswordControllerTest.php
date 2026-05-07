<?php

namespace Tests\Integration\Controllers\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // POST /users/{user}/reset-password — admin reset
    // -------------------------------------------------------------------------

    public function test_store_resets_password_for_authorized_admin(): void
    {
        $this->giveUserPermission($this->user, 'user:resetpasswordany');
        $target = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/users/{$target->id}/reset-password", [
                'new_password' => 'NewSecure123',
                'new_password_confirmation' => 'NewSecure123',
            ])
            ->assertStatus(200);

        $this->assertTrue(Hash::check('NewSecure123', $target->fresh()->password));
    }

    public function test_store_returns_403_without_permission(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/users/{$target->id}/reset-password", [
                'new_password' => 'NewSecure123',
                'new_password_confirmation' => 'NewSecure123',
            ])
            ->assertStatus(403);
    }

    public function test_store_returns_422_when_password_is_too_short(): void
    {
        $this->giveUserPermission($this->user, 'user:resetpasswordany');
        $target = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/users/{$target->id}/reset-password", [
                'new_password' => 'short',
                'new_password_confirmation' => 'short',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    public function test_store_returns_422_when_confirmation_does_not_match(): void
    {
        $this->giveUserPermission($this->user, 'user:resetpasswordany');
        $target = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/users/{$target->id}/reset-password", [
                'new_password' => 'NewSecure123',
                'new_password_confirmation' => 'Mismatch999',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    // -------------------------------------------------------------------------
    // PUT /users/me/change-password — self password change
    // -------------------------------------------------------------------------

    public function test_update_changes_own_password(): void
    {
        $this->giveUserPermission($this->user, 'user:changepassword');

        $this->actingAs($this->user, 'sanctum')
            ->putJson('/users/me/change-password', [
                'current_password' => 'password',
                'new_password' => 'NewSecure123!',
                'new_password_confirmation' => 'NewSecure123!',
            ])
            ->assertStatus(200);

        $this->assertTrue(Hash::check('NewSecure123!', $this->user->fresh()->password));
    }

    public function test_update_returns_422_for_wrong_current_password(): void
    {
        $this->giveUserPermission($this->user, 'user:changepassword');

        $this->actingAs($this->user, 'sanctum')
            ->putJson('/users/me/change-password', [
                'current_password' => 'wrongpassword',
                'new_password' => 'NewSecure123!',
                'new_password_confirmation' => 'NewSecure123!',
            ])
            ->assertStatus(422);
    }

    public function test_update_returns_422_when_new_password_is_too_short(): void
    {
        $this->giveUserPermission($this->user, 'user:changepassword');

        $this->actingAs($this->user, 'sanctum')
            ->putJson('/users/me/change-password', [
                'current_password' => 'password',
                'new_password' => 'short',
                'new_password_confirmation' => 'short',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    public function test_update_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->putJson('/users/me/change-password', [
                'current_password' => 'password',
                'new_password' => 'NewSecure123!',
                'new_password_confirmation' => 'NewSecure123!',
            ])
            ->assertStatus(403);
    }
}
