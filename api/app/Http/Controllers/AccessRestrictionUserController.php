<?php

namespace App\Http\Controllers;

use App\Http\Resources\user\UserCollection;
use App\Models\AccessRestriction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AccessRestrictionUserController extends Controller
{
    public function index(AccessRestriction $accessRestriction, Request $request): UserCollection
    {
        $this->authorize('listUsers', $accessRestriction);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $users = $accessRestriction->users()
            ->paginate($pagination);
        return new UserCollection($users);
    }

    public function store(Request $request, AccessRestriction $accessRestriction): Response
    {
        $this->authorize('addUser', $accessRestriction);
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ]);
        $accessRestriction->users()->syncWithoutDetaching($validated['user_ids']);
        return $this->created();
    }

    public function destroy(AccessRestriction $accessRestriction, Request $request): Response
    {
        $this->authorize('removeUser', $accessRestriction);
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);
        $accessRestriction->users()->detach($validated['user_ids']);
        return $this->noContent();
    }
}
