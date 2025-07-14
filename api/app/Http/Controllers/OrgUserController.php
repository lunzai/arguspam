<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Resources\user\UserCollection;
use App\Models\Org;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class OrgUserController extends Controller
{
    public function index(Org $org, Request $request): UserCollection
    {
        $this->authorize('orguser:viewany', $org);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        // $users = Cache::remember(
        //     CacheKey::ORG_USERS->key($org->id),
        //     config('cache.default_ttl'),
        //     function () use ($org, $pagination) {
        //         return $org->users()->paginate($pagination);
        //     }
        // );
        $users = $org->users()
            ->paginate($pagination);
        return new UserCollection($users);
    }

    public function store(Request $request, Org $org): Response
    {
        $this->authorize('orguser:create', $org);
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['required', 'exists:users,id'],
        ]);
        $org->users()->syncWithoutDetaching($validated['user_ids']);
        return $this->created();
    }

    public function destroy(Org $org, Request $request): Response
    {
        $this->authorize('orguser:delete', $org);
        $validated = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
        ]);
        $org->users()->detach($validated['user_ids']);
        return $this->noContent();
    }
}
