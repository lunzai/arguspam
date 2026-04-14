<?php

namespace Tests\Integration\Controllers\Org;

use App\Enums\Status;
use App\Models\Org;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrgControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // GET /orgs — index
    // -------------------------------------------------------------------------

    public function test_index_returns_orgs_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'org:view');
        Org::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/orgs');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure(['data', 'meta']);
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/orgs')
            ->assertStatus(403);
    }

    public function test_index_returns_401_when_unauthenticated(): void
    {
        $this->getJson('/orgs')->assertStatus(401);
    }

    // -------------------------------------------------------------------------
    // POST /orgs — store
    // -------------------------------------------------------------------------

    public function test_store_creates_org_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'org:create');

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/orgs', [
                'name' => 'Test Org',
                'description' => 'A test organization',
                'status' => Status::ACTIVE->value,
            ]);

        $this->assertApiSuccess($response, 201);
        $this->assertDatabaseHas('orgs', ['name' => 'Test Org']);
    }

    public function test_store_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->postJson('/orgs', [
                'name' => 'Test Org',
                'status' => Status::ACTIVE->value,
            ])
            ->assertStatus(403);
    }

    public function test_store_returns_422_when_name_is_missing(): void
    {
        $this->giveUserPermission($this->user, 'org:create');

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/orgs', [
                'status' => Status::ACTIVE->value,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_returns_422_when_name_is_too_short(): void
    {
        $this->giveUserPermission($this->user, 'org:create');

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/orgs', [
                'name' => 'A',
                'status' => Status::ACTIVE->value,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    public function test_store_returns_422_when_status_is_invalid(): void
    {
        $this->giveUserPermission($this->user, 'org:create');

        $this->actingAs($this->user, 'sanctum')
            ->postJson('/orgs', [
                'name' => 'Valid Org Name',
                'status' => 'invalid_status',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    // -------------------------------------------------------------------------
    // GET /orgs/{id} — show
    // -------------------------------------------------------------------------

    public function test_show_returns_org_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'org:view');
        $org = Org::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/orgs/{$org->id}");

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.attributes.name', $org->name);
    }

    public function test_show_returns_404_for_nonexistent_org(): void
    {
        $this->giveUserPermission($this->user, 'org:view');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/orgs/999999')
            ->assertStatus(404);
    }

    public function test_show_returns_403_without_permission(): void
    {
        $org = Org::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/orgs/{$org->id}")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // PUT /orgs/{id} — update
    // -------------------------------------------------------------------------

    public function test_update_modifies_org_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'org:updateany');
        $org = Org::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/orgs/{$org->id}", [
                'name' => 'Updated Name',
            ]);

        $this->assertApiSuccess($response);
        $this->assertDatabaseHas('orgs', ['id' => $org->id, 'name' => 'Updated Name']);
    }

    public function test_update_returns_403_without_permission(): void
    {
        $org = Org::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/orgs/{$org->id}", ['name' => 'Updated'])
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // DELETE /orgs/{id} — destroy
    // -------------------------------------------------------------------------

    public function test_destroy_soft_deletes_org_and_sets_deleted_by(): void
    {
        $this->giveUserPermission($this->user, 'org:deleteany');
        $org = Org::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/orgs/{$org->id}")
            ->assertStatus(204);

        $this->assertSoftDeleted('orgs', ['id' => $org->id]);
        $this->assertDatabaseHas('orgs', ['id' => $org->id, 'deleted_by' => $this->user->id]);
    }

    public function test_destroy_returns_403_without_permission(): void
    {
        $org = Org::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/orgs/{$org->id}")
            ->assertStatus(403);
    }
}
