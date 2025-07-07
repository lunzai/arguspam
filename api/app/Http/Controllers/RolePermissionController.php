<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Resources\Permission\PermissionCollection;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class RolePermissionController extends Controller
{
    public function index(Role $role): PermissionCollection
    {
        $this->authorize('rolepermission:index');
        $permissions = Cache::remember(
            CacheKey::ROLE_PERMISSIONS->key($role->id),
            config('cache.default_ttl'),
            function () use ($role) {
                return $role->permissions;
            }
        );
        return new PermissionCollection($permissions);
    }

    public function update(Request $request, Role $role): Response
    {
        $this->authorize('rolepermission:update', $role);
        $validated = $request->validate([
            'permission_ids' => ['array'],
            'permission_ids.*' => ['required', 'integer', 'exists:permissions,id'],
        ]);

        $role->permissions()->sync($validated['permission_ids']);
        Cache::forget(CacheKey::ROLE_PERMISSIONS->key($role->id));
        return $this->noContent();
    }

    // public function destroy(Role $role, Request $request): Response
    // {
    //     $this->authorize('rolepermission:delete', $role);
    //     $validated = $request->validate([
    //         'permission_ids' => ['required', 'array', 'min:1'],
    //         'permission_ids.*' => ['integer', 'exists:permissions,id'],
    //     ]);

    //     $role->permissions()->detach($validated['permission_ids']);

    //     return $this->noContent();
    // }
}
