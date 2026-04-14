<?php

namespace Tests\Integration\Controllers\Permission;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    // -------------------------------------------------------------------------
    // GET /permissions — index
    // -------------------------------------------------------------------------

    public function test_index_returns_permissions_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'permission:view');
        Permission::factory()->count(3)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/permissions');

        $this->assertApiSuccess($response);
        $response->assertJsonStructure(['data', 'meta']);
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson('/permissions')
            ->assertStatus(403);
    }

    public function test_index_returns_401_when_unauthenticated(): void
    {
        $this->getJson('/permissions')->assertStatus(401);
    }

    public function test_index_response_is_paginated(): void
    {
        $this->giveUserPermission($this->user, 'permission:view');
        Permission::factory()->count(5)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/permissions?per_page=2');

        $this->assertApiSuccess($response);
        $response->assertJsonPath('meta.per_page', 2);
    }

    // -------------------------------------------------------------------------
    // GET /permissions/{id} — show
    // -------------------------------------------------------------------------

    public function test_show_returns_permission_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'permission:view');
        $permission = Permission::factory()->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/permissions/{$permission->id}");

        $this->assertApiSuccess($response);
        $response->assertJsonPath('data.attributes.name', (string) $permission->name);
    }

    public function test_show_returns_404_for_nonexistent_permission(): void
    {
        $this->giveUserPermission($this->user, 'permission:view');

        $this->actingAs($this->user, 'sanctum')
            ->getJson('/permissions/999999')
            ->assertStatus(404);
    }

    public function test_show_returns_403_without_permission(): void
    {
        $permission = Permission::factory()->create();

        $this->actingAs($this->user, 'sanctum')
            ->getJson("/permissions/{$permission->id}")
            ->assertStatus(403);
    }
}
