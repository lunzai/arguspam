<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserRoleController extends Controller
{
    public function store(Request $request, User $user): Response
    {
        $this->authorize('userrole:create');
        $validated = $request->validate([
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['required', 'exists:roles,id'],
        ]);

        $user->roles()->sync($validated['role_ids']);

        return $this->created();
    }

    public function destroy(User $user, Request $request): Response
    {
        $this->authorize('userrole:delete');
        $validated = $request->validate([
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', 'exists:roles,id'],
        ]);

        $user->roles()->detach($validated['role_ids']);

        return $this->noContent();
    }
}
