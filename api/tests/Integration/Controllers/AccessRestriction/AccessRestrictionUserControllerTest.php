<?php

namespace Tests\Integration\Controllers\AccessRestriction;

use App\Enums\AccessRestrictionType;
use App\Enums\Status;
use App\Models\AccessRestriction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessRestrictionUserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private AccessRestriction $restriction;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->restriction = AccessRestriction::create([
            'name' => 'Test Restriction',
            'type' => AccessRestrictionType::IP_ADDRESS->value,
            'data' => ['allow' => ['10.0.0.1']],
            'status' => Status::ACTIVE->value,
            'weight' => 1,
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /access-restrictions/{id}/users — store
    // -------------------------------------------------------------------------

    public function test_store_adds_user_to_restriction(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:adduser');
        $target = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/access-restrictions/{$this->restriction->id}/users", [
                'user_ids' => [$target->id],
            ])
            ->assertStatus(201);

        $this->assertTrue($this->restriction->users()->where('users.id', $target->id)->exists());
    }

    public function test_store_returns_403_without_permission(): void
    {
        $target = User::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/access-restrictions/{$this->restriction->id}/users", [
                'user_ids' => [$target->id],
            ])
            ->assertStatus(403);
    }

    public function test_store_returns_422_for_missing_user_ids(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:adduser');

        $this->actingAs($this->user, 'sanctum')
            ->postJson("/access-restrictions/{$this->restriction->id}/users", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['user_ids']);
    }

    // -------------------------------------------------------------------------
    // DELETE /access-restrictions/{id}/users — destroy
    // -------------------------------------------------------------------------

    public function test_destroy_removes_user_from_restriction(): void
    {
        $this->giveUserPermission($this->user, 'accessrestriction:removeuser');
        $target = User::factory()->create();
        $this->restriction->users()->attach($target->id);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/access-restrictions/{$this->restriction->id}/users", [
                'user_ids' => [$target->id],
            ])
            ->assertStatus(204);

        $this->assertFalse($this->restriction->users()->where('users.id', $target->id)->exists());
    }

    public function test_destroy_returns_403_without_permission(): void
    {
        $target = User::factory()->create();
        $this->restriction->users()->attach($target->id);

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/access-restrictions/{$this->restriction->id}/users", [
                'user_ids' => [$target->id],
            ])
            ->assertStatus(403);
    }
}
