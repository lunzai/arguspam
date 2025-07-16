<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Filters\UserGroupFilter;
use App\Http\Requests\UserGroup\StoreUserGroupRequest;
use App\Http\Requests\UserGroup\UpdateUserGroupRequest;
use App\Http\Resources\UserGroup\UserGroupCollection;
use App\Http\Resources\UserGroup\UserGroupResource;
use App\Models\UserGroup;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserGroupController extends Controller
{
    use IncludeRelationships;

    public function index(UserGroupFilter $filter, Request $request): UserGroupCollection
    {
        $this->authorize('viewAny', UserGroup::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        // $userGroups = Cache::remember(
        //     CacheKey::USER_GROUPS->key($request->get(config('pam.org.request_attribute'))),
        //     config('cache.default_ttl'),
        //     function () use ($filter, $pagination) {
        //         return UserGroup::filter($filter)->paginate($pagination);
        //     }
        // );
        $userGroups = UserGroup::filter($filter)
            ->paginate($pagination);
        return new UserGroupCollection($userGroups);
    }

    public function store(StoreUserGroupRequest $request): UserGroupResource
    {
        $this->authorize('create', UserGroup::class);
        $validated = $request->validated();
        $userGroup = UserGroup::create($validated);

        return new UserGroupResource($userGroup);
    }

    public function show(string $id): UserGroupResource
    {
        $userGroupQuery = UserGroup::query();
        $this->applyIncludes($userGroupQuery, request());
        $userGroup = $userGroupQuery->findOrFail($id);
        $this->authorize('view', $userGroup);

        return new UserGroupResource($userGroup);
    }

    public function update(UpdateUserGroupRequest $request, UserGroup $userGroup): UserGroupResource
    {
        $this->authorize('update', $userGroup);
        $validated = $request->validated();
        $userGroup->update($validated);

        return new UserGroupResource($userGroup);
    }

    public function destroy(UserGroup $userGroup): Response
    {
        $this->authorize('delete', $userGroup);
        if ($userGroup->users()->exists()) {
            return $this->error('User group is not empty', 400);
        }
        $userGroup->deleted_by = Auth::id();
        $userGroup->save();
        $userGroup->delete();

        return $this->noContent();
    }
}
