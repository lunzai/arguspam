<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserGroup\UserGroupCollection;
use App\Models\Org;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrgUserGroupController extends Controller
{
    use IncludeRelationships;

    public function index(Org $org): UserGroupCollection
    {
        $userGroups = $org->userGroups()->paginate(config('pam.pagination.per_page'));

        return new UserGroupCollection($userGroups);
    }

    public function store(Request $request, Org $org): Response
    {
        $validated = $request->validate([
            'user_group_ids' => ['required', 'array'],
            'user_group_ids.*' => ['required', 'exists:user_groups,id'],
        ]);

        $org->userGroups()->attach($validated['user_group_ids']);

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function destroy(Org $org, string $userGroupId): Response
    {
        $org->userGroups()->detach($userGroupId);

        return response()->noContent();
    }
}
