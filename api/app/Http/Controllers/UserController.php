<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Filters\UserFilter;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Models\User;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    use IncludeRelationships;

    public function index(UserFilter $filter, Request $request): UserCollection
    {
        $this->authorize('viewAny', User::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        // $users = Cache::remember(
        //     CacheKey::USERS->key($request->get('page', 1)),
        //     config('cache.default_ttl'),
        //     function () use ($filter, $pagination) {
        //         return User::filter($filter)->paginate($pagination);
        //     }
        // );
        $users = User::filter($filter)
            ->paginate($pagination);
        return new UserCollection($users);
    }

    public function store(StoreUserRequest $request): UserResource
    {
        $this->authorize('create', User::class);
        $validated = $request->validated();
        $user = User::create($validated);

        return new UserResource($user);
    }

    public function show(string $id): UserResource
    {
        $userQuery = User::query();
        $this->applyIncludes($userQuery, request());
        $user = $userQuery->findOrFail($id);
        $this->authorize('view', $user);

        return new UserResource($user);
    }

    public function update(UpdateUserRequest $request, User $user): UserResource
    {
        $this->authorize('update', $user);
        $validated = $request->validated();
        $user->update($validated);

        return new UserResource($user);
    }

    public function destroy(User $user): Response
    {
        $this->authorize('delete', $user);
        $user->deleted_by = Auth::id();
        $user->save();
        $user->delete();

        return $this->noContent();
    }
}
