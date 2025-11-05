<?php

namespace App\Http\Controllers;

use App\Enums\AssetAccountType;
use App\Http\Filters\AssetFilter;
use App\Http\Requests\Asset\StoreAssetRequest;
use App\Http\Requests\Asset\UpdateAssetRequest;
use App\Http\Resources\Asset\AssetCollection;
use App\Http\Resources\Asset\AssetResource;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    use IncludeRelationships;

    public function index(AssetFilter $filter, Request $request): AssetCollection
    {
        $this->authorize('view', Asset::class);
        return Cache::remember('assets-' . $request->get(config('pam.org.request_attribute')), config('cache.default_ttl'), function () use ($filter, $request) {
            $pagination = $request->get('per_page', config('pam.pagination.per_page'));
            $assets = Asset::filter($filter)
                ->paginate($pagination);
            return new AssetCollection($assets);
        });
    }

    public function store(StoreAssetRequest $request): AssetResource
    {
        $this->authorize('create', Asset::class);
        $validated = $request->validated();
        $asset = DB::transaction(function () use ($validated) {
            $asset = Asset::create($validated);
            $account = new AssetAccount([
                'asset_id' => $asset->id,
                'username' => $validated['username'],
                'password' => $validated['password'],
                'type' => AssetAccountType::ADMIN,
                'is_active' => true,
            ]);
            $asset->accounts()->save($account);
            $asset->refresh();
            return $asset;
        });
        $asset->load('accounts');
        return new AssetResource($asset);
    }

    public function show(string $id): AssetResource
    {
        $asset = Cache::remember('asset-' . $id, config('cache.default_ttl'), function () use ($id) {
            $assetQuery = Asset::query();
            $this->applyIncludes($assetQuery, request());
            $asset = $assetQuery->findOrFail($id);
        });
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
        if ($asset->jitAccount()->exists()) {
            throw new \Exception('Asset has 1 or more temporary accounts. Please terminate them first.');
        }
        DB::transaction(function () use ($asset) {
            $asset->deleted_by = Auth::id();
            $asset->save();
            $asset->delete();
        });

        return $this->noContent();
    }
}
