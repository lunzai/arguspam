<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Resources\user\UserCollection;
use App\Models\AccessRestriction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class AccessRestrictionUserGroupController extends Controller
{
    public function index(AccessRestriction $accessRestriction, Request $request): UserCollection
    {
        $this->authorize('accessrestrictionusergroup:viewany', $accessRestriction);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        // $users = Cache::remember(
        //     CacheKey::ACCESS_RESTRICTION_USER_GROUPS->key($accessRestriction->id),
        //     config('cache.default_ttl'),
        //     function () use ($accessRestriction, $pagination) {
        //         return $accessRestriction->userGroups()->paginate($pagination);
        //     }
        // );
        $users = $accessRestriction->userGroups()
            ->paginate($pagination);
        return new UserCollection($users);
    }

    public function store(Request $request, AccessRestriction $accessRestriction): Response
    {
        $this->authorize('accessrestrictionusergroup:create', $accessRestriction);
        $validated = $request->validate([
            'user_group_ids' => ['required', 'array', 'min:1'],
            'user_group_ids.*' => ['required', 'exists:user_groups,id'],
        ]);
        $accessRestriction->userGroups()->syncWithoutDetaching($validated['user_group_ids']);
        return $this->created();
    }

    public function destroy(AccessRestriction $accessRestriction, Request $request): Response
    {
        $this->authorize('accessrestrictionusergroup:delete', $accessRestriction);
        $validated = $request->validate([
            'user_group_ids' => ['required', 'array', 'min:1'],
            'user_group_ids.*' => ['integer', 'exists:user_groups,id'],
        ]);
        $accessRestriction->userGroups()->detach($validated['user_group_ids']);
        return $this->noContent();
    }
}
