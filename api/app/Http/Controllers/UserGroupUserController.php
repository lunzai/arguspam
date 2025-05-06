<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        $validated = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['required', new UserExistedInOrg($userGroup->org_id)],
        ]);

        $userGroup->users()->attach($validated['user_ids']);

        return $this->created();
    }

    public function destroy(UserGroup $userGroup, User $user): Response
    {
        $userGroup->users()->detach($user);

        return $this->noContent();
    }
}
