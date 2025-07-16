<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Filters\AccessRestrictionFilter;
use App\Http\Requests\AccessRestriction\StoreAccessRestrictionRequest;
use App\Http\Requests\AccessRestriction\UpdateAccessRestrictionRequest;
use App\Http\Resources\AccessRestriction\AccessRestrictionCollection;
use App\Http\Resources\AccessRestriction\AccessRestrictionResource;
use App\Models\AccessRestriction;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AccessRestrictionController extends Controller
{
    use IncludeRelationships;

    public function index(AccessRestrictionFilter $filter, Request $request): AccessRestrictionCollection
    {
        $this->authorize('viewAny', AccessRestriction::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        // $accessRestrictions = Cache::remember(
        //     CacheKey::ACCESS_RESTRICTIONS->value,
        //     config('cache.default_ttl'),
        //     function () use ($filter, $pagination) {
        //         return AccessRestriction::filter($filter)->paginate($pagination);
        //     }
        // );
        $accessRestrictions = AccessRestriction::filter($filter)
            ->paginate($pagination);
        return new AccessRestrictionCollection($accessRestrictions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAccessRestrictionRequest $request): AccessRestrictionResource
    {
        $this->authorize('create', AccessRestriction::class);
        $validated = $request->validated();
        $accessRestriction = AccessRestriction::create($validated);
        Cache::forget(CacheKey::ACCESS_RESTRICTIONS->value);

        return new AccessRestrictionResource($accessRestriction);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): AccessRestrictionResource
    {
        $accessRestrictionQuery = AccessRestriction::query();
        $this->applyIncludes($accessRestrictionQuery, request());
        $accessRestriction = $accessRestrictionQuery->findOrFail($id);
        $this->authorize('view', $accessRestriction);

        return new AccessRestrictionResource($accessRestriction);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccessRestrictionRequest $request, AccessRestriction $accessRestriction): AccessRestrictionResource
    {
        $this->authorize('update', $accessRestriction);
        $validated = $request->validated();
        $accessRestriction->update($validated);
        Cache::forget(CacheKey::ACCESS_RESTRICTIONS->value);

        return new AccessRestrictionResource($accessRestriction);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccessRestriction $accessRestriction)
    {
        $this->authorize('delete', $accessRestriction);
        if ($accessRestriction->users()->exists()) {
            return $this->unprocessableEntity('Cannot delete access restriction with assigned users.');
        }
        if ($accessRestriction->userGroups()->exists()) {
            return $this->unprocessableEntity('Cannot delete access restriction with assigned user groups.');
        }
        $accessRestriction->delete();
        Cache::forget(CacheKey::ACCESS_RESTRICTIONS->value);
        return $this->noContent();
    }
}
