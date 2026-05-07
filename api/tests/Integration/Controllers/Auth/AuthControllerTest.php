<?php

namespace Tests\Integration\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FAQRCode\Google2FA;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // POST /auth/login — login (no 2FA)
    // -------------------------------------------------------------------------

    public function test_login_returns_token_for_valid_credentials(): void
    {
        $response = $this->postJson('/auth/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token']])
            ->assertJsonPath('data.requires_2fa', false);
    }

    public function test_login_returns_401_for_wrong_password(): void
    {
        $this->postJson('/auth/login', [
            'email' => $this->user->email,
            'password' => 'wrongpassword',
        ])->assertStatus(401);
    }

    public function test_login_returns_401_for_nonexistent_email(): void
    {
        $this->postJson('/auth/login', [
            'email' => 'nobody@example.com',
            'password' => 'password',
        ])->assertStatus(401);
    }

    public function test_login_returns_401_for_inactive_user(): void
    {
        $this->user->forceFill(['status' => 'inactive'])->save();

        $this->postJson('/auth/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ])->assertStatus(401);
    }

    public function test_login_returns_422_when_email_is_missing(): void
    {
        $this->postJson('/auth/login', [
            'password' => 'password',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_returns_422_when_password_is_missing(): void
    {
        $this->postJson('/auth/login', [
            'email' => $this->user->email,
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    // -------------------------------------------------------------------------
    // POST /auth/login — login with 2FA enrolled
    // -------------------------------------------------------------------------

    public function test_login_returns_temp_key_when_2fa_is_enrolled(): void
    {
        $this->user->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $response = $this->postJson('/auth/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.requires_2fa', true)
            ->assertJsonPath('data.token', null)
            ->assertJsonStructure(['data' => ['temp_key']]);
    }

    // -------------------------------------------------------------------------
    // POST /auth/2fa — verify 2FA
    // -------------------------------------------------------------------------

    public function test_verify_2fa_returns_token_for_valid_code(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        $this->user->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
        ])->save();

        // Login to get temp_key
        $loginResponse = $this->postJson('/auth/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        $tempKey = $loginResponse->json('data.temp_key');

        // Generate valid TOTP code
        $code = (new Google2FA)->getCurrentOtp($secret);

        $this->postJson('/auth/2fa', [
            'temp_key' => $tempKey,
            'code' => $code,
        ])->assertStatus(200)
            ->assertJsonStructure(['data' => ['token']]);
    }

    public function test_verify_2fa_returns_422_for_invalid_code(): void
    {
        $this->user->forceFill([
            'two_factor_enabled' => true,
            'two_factor_secret' => 'JBSWY3DPEHPK3PXP',
            'two_factor_confirmed_at' => now(),
        ])->save();

        $loginResponse = $this->postJson('/auth/login', [
            'email' => $this->user->email,
            'password' => 'password',
        ]);
        $tempKey = $loginResponse->json('data.temp_key');

        $this->postJson('/auth/2fa', [
            'temp_key' => $tempKey,
            'code' => '000000',
        ])->assertStatus(422);
    }

    public function test_verify_2fa_returns_401_for_expired_temp_key(): void
    {
        $this->postJson('/auth/2fa', [
            'temp_key' => str_repeat('a', 32),
            'code' => '123456',
        ])->assertStatus(401);
    }

    public function test_verify_2fa_returns_422_for_malformed_code(): void
    {
        $this->postJson('/auth/2fa', [
            'temp_key' => str_repeat('a', 32),
            'code' => 'abc',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['code']);
    }

    // -------------------------------------------------------------------------
    // POST /auth/logout
    // -------------------------------------------------------------------------

    public function test_logout_deletes_current_token(): void
    {
        $token = $this->user->createToken('auth_token')->plainTextToken;

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/auth/logout')
            ->assertStatus(200);
    }

    public function test_logout_returns_401_when_unauthenticated(): void
    {
        $this->postJson('/auth/logout')
            ->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // GET /auth/me
    // -------------------------------------------------------------------------

    public function test_me_returns_current_user_data(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/auth/me')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_me_returns_401_when_unauthenticated(): void
    {
        $this->getJson('/auth/me')
            ->assertStatus(401);
    }
}
