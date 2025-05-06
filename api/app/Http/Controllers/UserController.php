<?php

namespace App\Http\Controllers;

use App\Http\Filters\UserFilter;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    use IncludeRelationships;

    public function index(UserFilter $filter): UserCollection
    {
        $user = User::filter($filter);

        return new UserCollection(
            $user->paginate(config('pam.pagination.per_page'))
        );
    }

    public function store(StoreUserRequest $request): UserResource
    {
        $validated = $request->validated();
        $user = User::create($validated);

        return new UserResource($user);
    }

    public function show(string $id): UserResource
    {
        $user = User::query();
        $this->applyIncludes($user, request());

        return new UserResource($user->findOrFail($id));
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $validated = $request->validated();
        $user->update($validated);

        return new UserResource($user);
    }

    public function destroy(User $user): Response
    {
        $user->deleted_by = Auth::id();
        $user->save();
        $user->delete();

        return $this->noContent();
    }
}
