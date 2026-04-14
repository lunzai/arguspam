<?php

namespace Tests\Integration\Middleware;

use App\Enums\AccessRestrictionType;
use App\Enums\Status;
use App\Http\Middleware\EnforceUserAccessRestrictions;
use App\Models\User;
use App\Models\UserAccessRestriction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EnforceUserAccessRestrictionsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();

        Cache::forget("user_restrictions_{$this->user->id}");

        Route::middleware(['auth:sanctum', EnforceUserAccessRestrictions::class])
            ->get('/test-enforce-restrictions', fn () => response()->json(['ok' => true]));
    }

    private function createIpRestriction(array $allowedIps): UserAccessRestriction
    {
        return UserAccessRestriction::create([
            'user_id' => $this->user->id,
            'type' => AccessRestrictionType::IP_ADDRESS->value,
            'value' => ['allowed_ips' => $allowedIps],
            'status' => Status::ACTIVE->value,
        ]);
    }

    public function test_user_with_no_restrictions_passes_through(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/test-enforce-restrictions')
            ->assertStatus(200);
    }

    public function test_user_with_ip_restriction_matching_current_ip_passes(): void
    {
        Cache::forget("user_restrictions_{$this->user->id}");
        $this->createIpRestriction(['127.0.0.1']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/test-enforce-restrictions')
            ->assertStatus(200);
    }

    public function test_user_with_ip_restriction_not_matching_is_blocked(): void
    {
        Cache::forget("user_restrictions_{$this->user->id}");
        $this->createIpRestriction(['192.168.1.1']);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/test-enforce-restrictions')
            ->assertStatus(403)
            ->assertJsonFragment(['message' => 'Access denied due to access restrictions']);
    }

    public function test_unauthenticated_user_passes_through(): void
    {
        $this->getJson('/test-enforce-restrictions')
            ->assertStatus(401);
    }

    public function test_inactive_restriction_does_not_block(): void
    {
        Cache::forget("user_restrictions_{$this->user->id}");
        UserAccessRestriction::create([
            'user_id' => $this->user->id,
            'type' => AccessRestrictionType::IP_ADDRESS->value,
            'value' => ['allowed_ips' => ['192.168.1.1']],
            'status' => Status::INACTIVE->value,
        ]);

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/test-enforce-restrictions')
            ->assertStatus(200);
    }
}
