<?php

namespace Tests\Integration\Controllers\AccessRestriction;

use App\Enums\AccessRestrictionType;
use App\Enums\Status;
use App\Models\AccessRestriction;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessRestrictionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function validIpPayload(): array
    {
        return [
            'name' => 'Office IPs',
            'type' => AccessRestrictionType::IP_ADDRESS->value,
            'data' => [
                'allow' => ['192.168.1.1', '10.0.0.1'],
            ],
            'status' => Status::ACTIVE->value,
            'weight' => 1,
        ];
    }

    private function createRestriction(array $overrides = []): AccessRestriction
    {
        return AccessRestriction::create(array_merge([
            'name' => 'Test Restriction',
            'type' => AccessRestrictionType::IP_ADDRESS->value,
            'data' => ['allow' => ['192.168.1.1']],
            'status' => Status::ACTIVE->value,
            'weight' => 1,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // GET /access-restrictions — index
    // -------------------------------------------------------------------------

    public function test_index_lists_restrictions_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:view');
        $this->createRestriction();

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/access-restrictions')
            ->assertStatus(200)
            ->assertJsonStructure(['data']);
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/access-restrictions')
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // POST /access-restrictions — store
    // -------------------------------------------------------------------------

    public function test_store_creates_ip_restriction(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:create');

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/access-restrictions', $this->validIpPayload())
            ->assertStatus(201);

        $this->assertDatabaseHas('access_restrictions', ['name' => 'Office IPs']);
    }

    public function test_store_creates_time_window_restriction(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:create');

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/access-restrictions', [
                'name' => 'Business Hours',
                'type' => AccessRestrictionType::TIME_WINDOW->value,
                'data' => [
                    'allow' => [
                        [
                            'day_of_week' => [1, 2, 3, 4, 5],
                            'start_time' => '09:00',
                            'end_time' => '17:00',
                            'timezone' => 'UTC',
                        ],
                    ],
                ],
                'status' => Status::ACTIVE->value,
                'weight' => 2,
            ])
            ->assertStatus(201);
    }

    public function test_store_returns_422_for_missing_name(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:create');

        $payload = $this->validIpPayload();
        unset($payload['name']);

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/access-restrictions', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_returns_422_for_invalid_type(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:create');

        $payload = $this->validIpPayload();
        $payload['type'] = 'invalid_type';

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/access-restrictions', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['type']);
    }

    public function test_store_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/access-restrictions', $this->validIpPayload())
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // GET /access-restrictions/{id} — show
    // -------------------------------------------------------------------------

    public function test_show_returns_restriction_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:view');
        $restriction = $this->createRestriction();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/access-restrictions/{$restriction->id}")
            ->assertStatus(200);
    }

    public function test_show_returns_404_for_nonexistent_restriction(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:view');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/access-restrictions/999999')
            ->assertStatus(404);
    }

    public function test_show_returns_403_without_permission(): void
    {
        $restriction = $this->createRestriction();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/access-restrictions/{$restriction->id}")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // PUT /access-restrictions/{id} — update
    // -------------------------------------------------------------------------

    public function test_update_modifies_restriction(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:update');
        $restriction = $this->createRestriction();

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/access-restrictions/{$restriction->id}", [
                'name' => 'Updated Name',
                'status' => Status::ACTIVE->value,
            ])
            ->assertStatus(200);

        $this->assertEquals('Updated Name', $restriction->fresh()->name);
    }

    public function test_update_returns_403_without_permission(): void
    {
        $restriction = $this->createRestriction();

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/access-restrictions/{$restriction->id}", ['name' => 'Updated'])
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DELETE /access-restrictions/{id} — destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_restriction_with_no_assignments(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:deleteany');
        $restriction = $this->createRestriction();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/access-restrictions/{$restriction->id}")
            ->assertStatus(204);

        $this->assertDatabaseMissing('access_restrictions', ['id' => $restriction->id]);
    }

    public function test_destroy_returns_422_when_users_assigned(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:deleteany');
        $restriction = $this->createRestriction();
        $restriction->users()->attach($this->user->id);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/access-restrictions/{$restriction->id}")
            ->assertStatus(422);
    }

    public function test_destroy_returns_422_when_user_groups_assigned(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:deleteany');
        $restriction = $this->createRestriction();
        $group = UserGroup::factory()->create();
        $restriction->userGroups()->attach($group->id);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/access-restrictions/{$restriction->id}")
            ->assertStatus(422);
    }

    public function test_destroy_returns_403_without_permission(): void
    {
        $restriction = $this->createRestriction();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/access-restrictions/{$restriction->id}")
            ->assertStatus(403);
    }
}
