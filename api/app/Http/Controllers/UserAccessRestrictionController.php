<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserAccessRestriction\StoreUserAccessRestrictionRequest;
use App\Http\Requests\UserAccessRestriction\UpdateUserAccessRestrictionRequest;
use App\Http\Resources\UserAccessRestriction\UserAccessRestrictionCollection;
use App\Http\Resources\UserAccessRestriction\UserAccessRestrictionResource;
use App\Models\User;
use App\Models\UserAccessRestriction;
use App\Traits\ApiResponses;
use App\Traits\IncludeRelationships;

# TODO: Remove this
class UserAccessRestrictionController extends Controller
{
    use ApiResponses, IncludeRelationships;

    public function index(User $user): UserAccessRestrictionCollection
    {
        $this->authorize('viewAny', UserAccessRestriction::class);
        return new UserAccessRestrictionCollection($user->accessRestrictions);
    }

    public function store(StoreUserAccessRestrictionRequest $request, User $user): UserAccessRestrictionResource
    {
        $this->authorize('create', UserAccessRestriction::class);
        $validated = $request->validated();
        $restriction = $user->accessRestrictions()->create($validated);

        return new UserAccessRestrictionResource($restriction);
    }

    public function show(User $user, UserAccessRestriction $userAccessRestriction): UserAccessRestrictionResource
    {
        $this->authorize('view', $userAccessRestriction);
        // Laravel will automatically check if the restriction belongs to the user
        return new UserAccessRestrictionResource($userAccessRestriction);
    }

    public function update(UpdateUserAccessRestrictionRequest $request, User $user, UserAccessRestriction $userAccessRestriction): UserAccessRestrictionResource
    {
        $this->authorize('update', $userAccessRestriction);
        $validated = $request->validated();
        $userAccessRestriction->update($validated);

        return new UserAccessRestrictionResource($userAccessRestriction);
    }

    public function destroy(User $user, UserAccessRestriction $userAccessRestriction)
    {
        $this->authorize('delete', $userAccessRestriction);
        $userAccessRestriction->delete();
        return $this->noContent();
    }

    /**
     * Clear the user restrictions cache
     */
    // private function clearUserRestrictionsCache(int $userId): void
    // {
    //     Cache::forget("user_restrictions_{$userId}");
    // }
}
