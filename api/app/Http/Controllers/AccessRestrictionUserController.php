<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Resources\user\UserCollection;
use App\Models\AccessRestriction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class AccessRestrictionUserController extends Controller
{
    public function index(AccessRestriction $accessRestriction, Request $request): UserCollection
    {
        $this->authorize('accessrestrictionuser:viewany', $accessRestriction);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $users = Cache::remember(
            CacheKey::ACCESS_RESTRICTION_USERS->key($accessRestriction->id),
            config('cache.default_ttl'),
            function () use ($accessRestriction, $pagination) {
                return $accessRestriction->users()->paginate($pagination);
            }
        );
        return new UserCollection($users);
    }

    public function store(Request $request, AccessRestriction $accessRestriction): Response
    {
        $this->authorize('accessrestrictionuser:create', $accessRestriction);
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ]);
        $accessRestriction->users()->syncWithoutDetaching($validated['user_ids']);
        return $this->created();
    }

    public function destroy(AccessRestriction $accessRestriction, Request $request): Response
    {
        $this->authorize('accessrestrictionuser:delete', $accessRestriction);
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);
        $accessRestriction->users()->detach($validated['user_ids']);
        return $this->noContent();
    }
}
