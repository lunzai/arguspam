<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserRoleController extends Controller
{
    public function store(Request $request, User $user): Response
    {
        $validated = $request->validate([
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['required', 'exists:roles,id'],
        ]);

        $user->roles()->attach($validated['role_ids']);

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function destroy(User $user, Role $role): Response
    {
        $user->roles()->detach($role);

        return response()->noContent(Response::HTTP_NO_CONTENT);
    }
}
