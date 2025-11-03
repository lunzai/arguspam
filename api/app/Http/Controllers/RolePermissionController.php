<?php

namespace App\Http\Controllers;

use App\Http\Resources\Permission\PermissionCollection;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RolePermissionController extends Controller
{
    public function index(Role $role, Request $request): PermissionCollection
    {
        $this->authorize('listPermissions', $role);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $permissions = $role->permissions()
            ->paginate($pagination);
        return new PermissionCollection($permissions);
    }

    /**
     * Update & sync role permissions
     */
    public function update(Request $request, Role $role): Response
    {
        $this->authorize('updatePermissions', $role);
        $validated = $request->validate([
            'permission_ids' => ['array'],
            'permission_ids.*' => ['required', 'integer', 'exists:permissions,id'],
        ]);
        $role->permissions()->sync($validated['permission_ids']);
        return $this->noContent();
    }
}
