<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserGroup\StoreUserGroupRequest;
use App\Http\Requests\UserGroup\UpdateUserGroupRequest;
use App\Http\Resources\UserGroup\UserGroupCollection;
use App\Http\Resources\UserGroup\UserGroupResource;
use App\Models\UserGroup;
use Illuminate\Support\Facades\Auth;

class UserGroupController extends Controller
{
    public function index()
    {
        return new UserGroupCollection(
            UserGroup::paginate(config('constants.pagination.per_page'))
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
        return new UserGroupResource(UserGroup::findOrFail($id));
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
