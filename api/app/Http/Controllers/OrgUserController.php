<?php

namespace App\Http\Controllers;

use App\Http\Resources\user\UserCollection;
use App\Models\Org;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrgUserController extends Controller
{
    /**
     * List users in org
     */
    public function index(Org $org, Request $request): UserCollection
    {
        $this->authorize('listUsers', $org);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $users = $org->users()
            ->paginate($pagination);
        return new UserCollection($users);
    }

    /**
     * Add users to org
     */
    public function store(Request $request, Org $org): Response
    {
        $this->authorize('addUser', $org);
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ]);
        $org->users()->syncWithoutDetaching($validated['user_ids']);
        return $this->created();
    }

    /**
     * Remove users from org
     */
    public function destroy(Org $org, Request $request): Response
    {
        $this->authorize('removeUser', $org);
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);
        $org->users()->detach($validated['user_ids']);
        return $this->noContent();
    }
}
