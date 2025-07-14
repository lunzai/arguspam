<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Resources\AssetAccessGrant\AssetAccessGrantCollection;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

// TODO: REWRITE
class AssetAccessGrantController extends Controller
{
    use IncludeRelationships;

    public function index(Asset $asset, Request $request): AssetAccessGrantCollection
    {
        $this->authorize('viewAny', AssetAccessGrant::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        // $grants = Cache::remember(
        //     CacheKey::ASSET_ACCESS_GRANTS->value,
        //     config('cache.default_ttl'),
        //     function () use ($asset, $pagination) {
        //         return $asset->accessGrants()->paginate($pagination);
        //     }
        // );
        $grants = $asset->accessGrants()
            ->paginate($pagination);
        return new AssetAccessGrantCollection($grants);
    }

    public function store(Request $request, Asset $asset): Response
    {
        $this->authorize('create', AssetAccessGrant::class);
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'string'], // You might want to add enum validation here
        ]);

        $asset->accessGrants()->create($validated);

        return $this->created();
    }

    public function update(Request $request, Asset $asset, string $grantId): Response
    {
        $this->authorize('update', AssetAccessGrant::class);
        $validated = $request->validate([
            'role' => ['required', 'string'], // You might want to add enum validation here
        ]);

        $asset->accessGrants()->findOrFail($grantId)->update($validated);

        return $this->noContent();
    }

    public function destroy(Asset $asset, string $grantId): Response
    {
        $this->authorize('delete', AssetAccessGrant::class);
        $asset->accessGrants()->findOrFail($grantId)->delete();

        return $this->noContent();
    }
}
