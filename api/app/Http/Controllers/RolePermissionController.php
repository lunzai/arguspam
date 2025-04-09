<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RolePermissionController extends Controller
{
    public function store(Request $request, Role $role): Response
    {
        $validated = $request->validate([
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['required', 'exists:permissions,id'],
        ]);

        $role->permissions()->attach($validated['permission_ids']);

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function destroy(Role $role, Permission $permission): Response
    {
        $role->permissions()->detach($permission);

        return response()->noContent(Response::HTTP_NO_CONTENT);
    }
}
