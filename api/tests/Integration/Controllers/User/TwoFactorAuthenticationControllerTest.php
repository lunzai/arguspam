<?php

namespace Tests\Integration\Controllers\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TwoFactorAuthenticationControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        // Ensure 2FA is off by default (two_factor_enabled is guarded)
        $this->user->forceFill([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    // -------------------------------------------------------------------------
    // POST /users/{user}/2fa — enable (store)
    // -------------------------------------------------------------------------

    public function test_store_enables_two_factor_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'user:enrolltwofactorauthentication');

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/users/{$this->user->id}/2fa")
            ->assertStatus(201);

        $this->assertTrue($this->user->fresh()->two_factor_enabled);
    }

    public function test_store_returns_422_when_already_enabled(): void
    {
        $this->giveUserPermission($this->user, 'user:enrolltwofactorauthentication');
        // two_factor_enabled is guarded, must use forceFill
        $this->user->forceFill(['two_factor_enabled' => true])->save();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/users/{$this->user->id}/2fa")
            ->assertStatus(422);
    }

    public function test_store_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson("/users/{$this->user->id}/2fa")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // GET /users/{user}/2fa — show QR code
    // -------------------------------------------------------------------------

    public function test_show_returns_qr_code_when_enabled_but_not_confirmed(): void
    {
        $this->giveUserPermission($this->user, 'user:enrolltwofactorauthentication');
        // Use forceFill + a valid Google2FA secret (16-char base32)
        $this->user->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => null,
        ])->save();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/users/{$this->user->id}/2fa");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['qr_code']]);
    }

    public function test_show_returns_422_when_not_enabled(): void
    {
        $this->giveUserPermission($this->user, 'user:enrolltwofactorauthentication');

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/users/{$this->user->id}/2fa")
            ->assertStatus(422);
    }

    public function test_show_returns_403_for_other_user_without_any_permission(): void
    {
        $other = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/users/{$other->id}/2fa")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DELETE /users/{user}/2fa — disable (destroy)
    // -------------------------------------------------------------------------

    public function test_destroy_disables_two_factor(): void
    {
        $this->giveUserPermission($this->user, 'user:enrolltwofactorauthentication');
        $this->user->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
        ])->save();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/users/{$this->user->id}/2fa")
            ->assertStatus(204);

        $fresh = $this->user->fresh();
        $this->assertFalse($fresh->two_factor_enabled);
        $this->assertNull($fresh->two_factor_secret);
    }

    public function test_destroy_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/users/{$this->user->id}/2fa")
            ->assertStatus(403);
    }
}
