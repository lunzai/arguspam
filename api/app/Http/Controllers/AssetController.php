<?php

namespace App\Http\Controllers;

use App\Http\Requests\Asset\StoreAssetRequest;
use App\Http\Requests\Asset\UpdateAssetRequest;
use App\Http\Resources\Asset\AssetCollection;
use App\Http\Resources\Asset\AssetResource;
use App\Models\Asset;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    public function index()
    {
        return new AssetCollection(
            Asset::paginate(config('constants.pagination.per_page'))
        );
    }

    public function store(StoreAssetRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();
        $asset = Asset::create($validated);

        return new AssetResource($asset);
    }

    public function show(string $id)
    {
        return new AssetResource(Asset::findOrFail($id));
    }

    public function update(UpdateAssetRequest $request, string $id)
    {
        $asset = Asset::findOrFail($id);
        $validated = $request->validated();
        $validated['updated_by'] = Auth::id();
        $asset->update($validated);

        return new AssetResource($asset);
    }

    public function destroy(string $id)
    {
        $asset = Asset::findOrFail($id);
        $asset->deleted_by = Auth::id();
        $asset->save();
        $asset->delete();

        return response()->noContent();
    }
}
