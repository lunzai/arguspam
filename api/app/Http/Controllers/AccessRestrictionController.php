<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Filters\AccessRestrictionFilter;
use App\Http\Resources\AccessRestriction\AccessRestrictionCollection;
use App\Http\Requests\AccessRestriction\StoreAccessRestrictionRequest;
use App\Http\Requests\AccessRestriction\UpdateAccessRestrictionRequest;
use App\Models\AccessRestriction;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AccessRestrictionController extends Controller
{
    use IncludeRelationships;

    public function index(AccessRestrictionFilter $filter, Request $request) : AccessRestrictionCollection
    {
        $this->authorize('viewAny', AccessRestriction::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $accessRestrictions = Cache::remember(
            CacheKey::ACCESS_RESTRICTIONS->value,
            config('cache.default_ttl'),
            function () use ($filter, $pagination) {
                return AccessRestriction::filter($filter)->paginate($pagination);
            }
        );
        return new AccessRestrictionCollection($accessRestrictions);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAccessRestrictionRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(AccessRestriction $accessRestriction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAccessRestrictionRequest $request, AccessRestriction $accessRestriction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccessRestriction $accessRestriction)
    {
        //
    }
}
