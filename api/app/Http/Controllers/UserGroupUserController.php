<?php

namespace App\Http\Controllers;

use App\Http\Resources\User\UserCollection;
use App\Models\UserGroup;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

# TODO: REWRITE
class UserGroupUserController extends Controller
{
    use IncludeRelationships;

    public function index(int $userGroupId): UserCollection
    {
        $userGroup = UserGroup::findOrFail($userGroupId);
        $users = $userGroup->users()->paginate(config('pam.pagination.per_page'));

        return new UserCollection($users);
    }

    public function store(Request $request, int $userGroupId): Response
    {
        $validated = $request->validate([
            'user_ids' => ['required', 'array'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ]);

        $userGroup = UserGroup::findOrFail($userGroupId);
        $userGroup->users()->attach($validated['user_ids']);

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function destroy(int $userGroupId, int $userId): Response
    {
        $userGroup = UserGroup::findOrFail($userGroupId);
        $userGroup->users()->detach($userId);

        return response()->noContent();
    }
}
