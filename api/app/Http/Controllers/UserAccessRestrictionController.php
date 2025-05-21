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
use Illuminate\Support\Facades\Cache;

class UserAccessRestrictionController extends Controller
{
    use ApiResponses, IncludeRelationships;

    public function index(User $user): UserAccessRestrictionCollection
    {
        return new UserAccessRestrictionCollection($user->accessRestrictions);
    }

    public function store(StoreUserAccessRestrictionRequest $request, User $user): UserAccessRestrictionResource
    {
        $validated = $request->validated();
        $restriction = $user->accessRestrictions()->create($validated);
        
        return new UserAccessRestrictionResource($restriction);
    }

    public function show(User $user, UserAccessRestriction $userAccessRestriction): UserAccessRestrictionResource
    {
        // Laravel will automatically check if the restriction belongs to the user
        return new UserAccessRestrictionResource($userAccessRestriction);
    }

    public function update(UpdateUserAccessRestrictionRequest $request, User $user, UserAccessRestriction $userAccessRestriction): UserAccessRestrictionResource
    {
        $validated = $request->validated();
        $userAccessRestriction->update($validated);
        
        return new UserAccessRestrictionResource($userAccessRestriction);
    }

    public function destroy(User $user, UserAccessRestriction $userAccessRestriction)
    {
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
