<?php

namespace App\Http\Controllers;

use App\Http\Resources\user\UserCollection;
use App\Models\AccessRestriction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AccessRestrictionUserGroupController extends Controller
{
    public function index(AccessRestriction $accessRestriction, Request $request): UserCollection
    {
        $this->authorize('listUserGroups', $accessRestriction);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $users = $accessRestriction->userGroups()
            ->paginate($pagination);
        return new UserCollection($users);
    }

    public function addUserGroup(Request $request, AccessRestriction $accessRestriction): Response
    {
        $this->authorize('addUserGroup', $accessRestriction);
        $validated = $request->validate([
            'user_group_ids' => ['required', 'array', 'min:1'],
            'user_group_ids.*' => ['required', 'exists:user_groups,id'],
        ]);
        $accessRestriction->userGroups()->syncWithoutDetaching($validated['user_group_ids']);
        return $this->created();
    }

    public function removeUserGroup(AccessRestriction $accessRestriction, Request $request): Response
    {
        $this->authorize('removeUserGroup', $accessRestriction);
        $validated = $request->validate([
            'user_group_ids' => ['required', 'array', 'min:1'],
            'user_group_ids.*' => ['integer', 'exists:user_groups,id'],
        ]);
        $accessRestriction->userGroups()->detach($validated['user_group_ids']);
        return $this->noContent();
    }
}
