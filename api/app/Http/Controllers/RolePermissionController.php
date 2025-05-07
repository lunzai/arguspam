<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RolePermissionController extends Controller
{
    public function store(Request $request, Role $role): Response
    {
        $validated = $request->validate([
            'permission_ids' => ['required', 'array', 'min:1'],
            'permission_ids.*' => ['required', 'exists:permissions,id'],
        ]);

        $role->permissions()->syncWithoutDetaching($validated['permission_ids']);

        return $this->created();
    }

    public function destroy(Role $role, Request $request): Response
    {
        $validated = $request->validate([
            'permission_ids' => ['required', 'array', 'min:1'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->permissions()->detach($validated['permission_ids']);

        return $this->noContent();
    }
}
