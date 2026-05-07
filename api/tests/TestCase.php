<?php

namespace Tests;

use App\Models\Org;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    /**
     * Authenticate as the given user and set the org context header.
     */
    protected function actingAsWithOrg(User $user, Org $org): static
    {
        return $this->actingAs($user, 'sanctum')
            ->withHeader(config('pam.org.request_header', 'x-organization-id'), (string) $org->id);
    }

    /**
     * Give a user a named permission via a temporary role.
     */
    protected function giveUserPermission(User $user, string $permissionName): void
    {
        $permission = Permission::firstOrCreate(['name' => $permissionName]);
        $role = Role::factory()->create();
        $role->permissions()->sync([$permission->id]);
        $user->roles()->attach($role->id);
        $user->clearUserRolePermissionCache();
    }

    /**
     * Assert a JSON API response has the standard envelope shape.
     */
    protected function assertApiSuccess(TestResponse $response, int $status = 200): void
    {
        $response->assertStatus($status)->assertJsonStructure(['data']);
    }
}
