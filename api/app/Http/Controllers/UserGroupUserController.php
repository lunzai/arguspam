<?php

namespace App\Http\Controllers;

use App\Models\UserGroup;
use App\Rules\UserExistedInOrg;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserGroupUserController extends Controller
{
    use IncludeRelationships;

    public function store(Request $request, UserGroup $userGroup): Response
    {
        $this->authorize('usergroupuser:create', $userGroup);
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', new UserExistedInOrg($userGroup->org_id)],
        ]);

        $userGroup->users()->syncWithoutDetaching($validated['user_ids']);

        return $this->created();
    }

    public function destroy(UserGroup $userGroup, Request $request): Response
    {
        $this->authorize('usergroupuser:delete', $userGroup);
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);

        $userGroup->users()->detach($validated['user_ids']);

        return $this->noContent();
    }
}
