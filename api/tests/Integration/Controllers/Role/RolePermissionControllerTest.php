<?php

namespace Tests\Integration\Controllers\Role;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->role = Role::factory()->create(['is_default' => false]);
    }

    // -------------------------------------------------------------------------
    // GET /roles/{role}/permissions — index
    // -------------------------------------------------------------------------

    public function test_index_lists_role_permissions_for_authorized_user(): void
    {
        $this->giveUserPermission($this->user, 'role:listpermissions');
        $permissions = Permission::factory()->count(2)->create();
        $this->role->permissions()->sync($permissions->pluck('id'));

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/roles/{$this->role->id}/permissions");

        $this->assertApiSuccess($response);
        $response->assertJsonCount(2, 'data');
    }

    public function test_index_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->getJson("/roles/{$this->role->id}/permissions")
            ->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // PUT /roles/{role}/permissions — update (sync)
    // -------------------------------------------------------------------------

    public function test_update_syncs_permissions_to_role(): void
    {
        $this->giveUserPermission($this->user, 'role:updatepermissions');
        $permissions = Permission::factory()->count(3)->create();

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/roles/{$this->role->id}/permissions", [
                'permission_ids' => $permissions->pluck('id')->toArray(),
            ])
            ->assertStatus(204);

        $this->assertEquals(3, $this->role->permissions()->count());
    }

    public function test_update_clears_all_permissions_when_empty_array_given(): void
    {
        $this->giveUserPermission($this->user, 'role:updatepermissions');
        $permission = Permission::factory()->create();
        $this->role->permissions()->sync([$permission->id]);

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/roles/{$this->role->id}/permissions", [
                'permission_ids' => [],
            ])
            ->assertStatus(204);

        $this->assertEquals(0, $this->role->permissions()->count());
    }

    public function test_update_returns_422_for_invalid_permission_ids(): void
    {
        $this->giveUserPermission($this->user, 'role:updatepermissions');

        $this->actingAs($this->user, 'sanctum')
            ->putJson("/roles/{$this->role->id}/permissions", [
                'permission_ids' => [999999],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['permission_ids.0']);
    }

    public function test_update_returns_403_without_permission(): void
    {
        $this->actingAs($this->user, 'sanctum')
            ->putJson("/roles/{$this->role->id}/permissions", [
                'permission_ids' => [],
            ])
            ->assertStatus(403);
    }

    public function test_user_with_synced_role_has_correct_permissions(): void
    {
        $this->giveUserPermission($this->user, 'role:updatepermissions');
        $targetPermission = Permission::firstOrCreate(['name' => 'asset:view']);
        $targetRole = Role::factory()->create();

        // Sync the permission to the role
        $this->actingAs($this->user, 'sanctum')
            ->putJson("/roles/{$targetRole->id}/permissions", [
                'permission_ids' => [$targetPermission->id],
            ])
            ->assertStatus(204);

        $targetUser = User::factory()->create();
        $targetUser->roles()->attach($targetRole->id);
        $targetUser->clearUserRolePermissionCache();

        $this->assertTrue($targetUser->fresh()->hasAnyPermission('asset:view'));
    }
}
