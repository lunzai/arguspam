<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserRoleController extends Controller
{
    public function store(Request $request, User $user): Response
    {
        $validated = $request->validate([
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['required', 'exists:roles,id'],
        ]);

        $user->roles()->syncWithoutDetaching($validated['role_ids']);

        return $this->created();
    }

    public function destroy(User $user, Request $request): Response
    {
        $validated = $request->validate([
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $user->roles()->detach($validated['role_ids']);

        return $this->noContent();
    }
}
