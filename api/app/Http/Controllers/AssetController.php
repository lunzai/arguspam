<?php

namespace App\Http\Controllers;

use App\Http\Filters\AssetFilter;
use App\Http\Requests\Asset\StoreAssetRequest;
use App\Http\Requests\Asset\UpdateAssetRequest;
use App\Http\Resources\Asset\AssetCollection;
use App\Http\Resources\Asset\AssetResource;
use App\Models\Asset;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    use IncludeRelationships;

    public function index(AssetFilter $filter): AssetCollection
    {
        $this->authorize('viewAny', Asset::class);
        $asset = Asset::filter($filter);

        return new AssetCollection(
            Asset::paginate(config('pam.pagination.per_page'))
        );
    }

    public function store(StoreAssetRequest $request): AssetResource
    {
        $this->authorize('create', Asset::class);
        $validated = $request->validated();
        $asset = Asset::create($validated);

        return new AssetResource($asset);
    }

    public function show(string $id): AssetResource
    {
        $assetQuery = Asset::query();
        $this->applyIncludes($assetQuery, request());
        $asset = $assetQuery->findOrFail($id);
        $this->authorize('view', $asset);

        return new AssetResource($asset);
    }

    public function update(UpdateAssetRequest $request, string $id): AssetResource
    {
        $asset = Asset::findOrFail($id);
        $this->authorize('update', $asset);
        $validated = $request->validated();
        $asset->update($validated);

        return new AssetResource($asset);
    }

    public function destroy(string $id): Response
    {
        $asset = Asset::findOrFail($id);
        $this->authorize('delete', $asset);
        $asset->deleted_by = Auth::id();
        $asset->save();
        $asset->delete();

        return $this->noContent();
    }
}
