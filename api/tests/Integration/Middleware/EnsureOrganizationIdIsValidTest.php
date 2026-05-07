<?php

namespace Tests\Integration\Middleware;

use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class EnsureOrganizationIdIsValidTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Org $org;

    protected function setUp(): void
    {
        parent::setUp();
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
    }

    /**
     * Hit an org-scoped route without needing any specific permission by
     * checking the middleware error responses, which fire before policy checks.
     */
    private function hitOrgRoute(array $headers = []): TestResponse
    {
        return $this->actingAs($this->user, 'sanctum')
            ->withHeaders($headers)
            ->getJson('/user-groups');
    }

    // -------------------------------------------------------------------------
    // Missing org header
    // -------------------------------------------------------------------------

    public function test_returns_400_when_org_header_missing(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/user-groups')
            ->assertStatus(400)
            ->assertJsonPath('message', 'Organization ID is required');
    }

    // -------------------------------------------------------------------------
    // Valid org membership
    // -------------------------------------------------------------------------

    public function test_passes_through_when_user_belongs_to_org(): void
    {
        // Middleware passes; controller then enforces policy.
        // Without usergroup:view permission → 403 from policy (not middleware).
        $response = $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/user-groups');

        // Must NOT be the middleware 400 or its 403
        $this->assertNotEquals(400, $response->status());
        // The controller policy 403 has a different body than the middleware 403
        if ($response->status() === 403) {
            $this->assertStringNotContainsString(
                'Organization ID header is missing',
                $response->content()
            );
        }
    }

    public function test_passes_through_and_returns_200_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:view');

        $this->actingAsWithOrg($this->user, $this->org)
            ->getJson('/user-groups')
            ->assertStatus(200);
    }

    // -------------------------------------------------------------------------
    // User not in org
    // -------------------------------------------------------------------------

    public function test_returns_403_when_user_not_in_org(): void
    {
        $otherOrg = Org::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->withHeader('x-organization-id', $otherOrg->id)
            ->getJson('/user-groups')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Unauthorized access to organization');
    }

    public function test_returns_403_for_non_existent_org_id(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->withHeader('x-organization-id', 999999)
            ->getJson('/user-groups')
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // Unauthenticated requests
    // -------------------------------------------------------------------------

    public function test_returns_401_when_unauthenticated(): void
    {
        $this->getJson('/user-groups')
            ->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // Membership changes
    // -------------------------------------------------------------------------

    public function test_user_removed_from_org_is_blocked(): void
    {
        $this->giveUserPermission($this->user, 'usergroup:view');

        // User was in org at setUp — remove them now
        $this->org->users()->detach($this->user->id);

        $this->actingAs($this->user, 'sanctum')
            ->withHeader('x-organization-id', $this->org->id)
            ->getJson('/user-groups')
            ->assertStatus(403)
            ->assertJsonPath('message', 'Unauthorized access to organization');
    }

    public function test_user_added_to_org_can_access_it(): void
    {
        $newUser = User::factory()->create();
        // Not in org yet
        $this->actingAs($newUser, 'sanctum')
            ->withHeader('x-organization-id', $this->org->id)
            ->getJson('/user-groups')
            ->assertStatus(403);

        // Add to org
        $this->org->users()->attach($newUser->id);
        $this->giveUserPermission($newUser, 'usergroup:view');

        $this->actingAs($newUser, 'sanctum')
            ->withHeader('x-organization-id', $this->org->id)
            ->getJson('/user-groups')
            ->assertStatus(200);
    }
}
