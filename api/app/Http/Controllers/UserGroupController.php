<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserGroup\StoreUserGroupRequest;
use App\Http\Requests\UserGroup\UpdateUserGroupRequest;
use App\Http\Resources\UserGroup\UserGroupCollection;
use App\Http\Resources\UserGroup\UserGroupResource;
use App\Models\UserGroup;
use App\Traits\IsExpandable;
use Illuminate\Support\Facades\Auth;

class UserGroupController extends Controller
{
    use IsExpandable;

    public function index()
    {
        $userGroup = UserGroup::query();
        $this->applyExpands($userGroup);
        return new UserGroupCollection(
            $userGroup->paginate(config('constants.pagination.per_page'))
        );
    }

    public function store(StoreUserGroupRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();
        $userGroup = UserGroup::create($validated);

        return new UserGroupResource($userGroup);
    }

    public function show(string $id)
    {
        $userGroup = UserGroup::query();
        $this->applyExpands($userGroup);
        return new UserGroupResource($userGroup->findOrFail($id));
    }

    public function update(UpdateUserGroupRequest $request, string $id)
    {
        $userGroup = UserGroup::findOrFail($id);
        $validated = $request->validated();
        $validated['updated_by'] = Auth::id();
        $userGroup->update($validated);

        return new UserGroupResource($userGroup);
    }

    public function destroy(string $id)
    {
        $userGroup = UserGroup::findOrFail($id);
        $userGroup->deleted_by = Auth::id();
        $userGroup->save();
        $userGroup->delete();

        return response()->noContent();
    }
}
