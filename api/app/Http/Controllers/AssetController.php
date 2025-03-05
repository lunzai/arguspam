<?php

namespace App\Http\Controllers;

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

    public function index(): AssetCollection
    {
        $asset = Asset::query();
        // $this->applyExpands($asset);

        return new AssetCollection(
            Asset::paginate(config('constants.pagination.per_page'))
        );
    }

    public function store(StoreAssetRequest $request): AssetResource
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();
        $asset = Asset::create($validated);

        return new AssetResource($asset);
    }

    public function show(string $id): AssetResource
    {
        $asset = Asset::query();
        $this->applyIncludes($asset, request());

        return new AssetResource($asset->findOrFail($id));
    }

    public function update(UpdateAssetRequest $request, string $id): AssetResource
    {
        $asset = Asset::findOrFail($id);
        $validated = $request->validated();
        $validated['updated_by'] = Auth::id();
        $asset->update($validated);

        return new AssetResource($asset);
    }

    public function destroy(string $id): Response
    {
        $asset = Asset::findOrFail($id);
        $asset->deleted_by = Auth::id();
        $asset->save();
        $asset->delete();

        return response()->noContent();
    }
}
