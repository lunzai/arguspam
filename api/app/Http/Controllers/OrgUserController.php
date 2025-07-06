<?php

namespace App\Http\Controllers;

use App\Http\Resources\user\UserCollection;
use App\Models\Org;
use App\Models\OrgUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrgUserController extends Controller
{
    public function index(Org $org): UserCollection
    {
        $this->authorize('orguser:viewany', $org);
        $users = $org->users;
        return new UserCollection($users);
    }

    public function store(Request $request, Org $org): Response
    {
        $this->authorize('orguser:create', $org);
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ]);

        $org->users()->syncWithoutDetaching($validated['user_ids']);

        return $this->created();
    }

    public function destroy(Org $org, Request $request): Response
    {
        $this->authorize('orguser:delete', $org);
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $org->users()->detach($validated['user_ids']);

        return $this->noContent();
    }
}
