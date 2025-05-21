<?php

namespace App\Http\Controllers;

use App\Http\Filters\UserGroupFilter;
use App\Http\Requests\UserGroup\StoreUserGroupRequest;
use App\Http\Requests\UserGroup\UpdateUserGroupRequest;
use App\Http\Resources\UserGroup\UserGroupCollection;
use App\Http\Resources\UserGroup\UserGroupResource;
use App\Models\UserGroup;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

# TODO: refactor like UserAccessRestrictionController
class UserGroupController extends Controller
{
    use IncludeRelationships;

    public function index(UserGroupFilter $filter): UserGroupCollection
    {
        $userGroup = UserGroup::filter($filter);

        return new UserGroupCollection(
            $userGroup->paginate(config('pam.pagination.per_page'))
        );
    }

    public function store(StoreUserGroupRequest $request): UserGroupResource
    {
        $validated = $request->validated();
        $userGroup = UserGroup::create($validated);

        return new UserGroupResource($userGroup);
    }

    public function show(string $id): UserGroupResource
    {
        $userGroup = UserGroup::query();
        $this->applyIncludes($userGroup, request());

        return new UserGroupResource($userGroup->findOrFail($id));
    }

    public function update(UpdateUserGroupRequest $request, UserGroup $userGroup): UserGroupResource
    {
        $validated = $request->validated();
        $userGroup->update($validated);

        return new UserGroupResource($userGroup);
    }

    public function destroy(UserGroup $userGroup): Response
    {
        $userGroup->deleted_by = Auth::id();
        $userGroup->save();
        $userGroup->delete();

        return $this->noContent();
    }
}
