<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssetAccount\StoreAssetAccountRequest;
use App\Http\Resources\AssetAccount\AssetAccountCollection;
use App\Models\Asset;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Response;

class AssetAccountController extends Controller
{
    use IncludeRelationships;

    public function index(Asset $asset): AssetAccountCollection
    {
        $accounts = $asset->accounts()->paginate(config('pam.pagination.per_page'));

        return new AssetAccountCollection($accounts);
    }

    public function store(StoreAssetAccountRequest $request, Asset $asset): Response
    {
        $validated = $request->validated();

        $asset->accounts()->attach($validated['account_ids']);

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function destroy(Asset $asset, string $accountId): Response
    {
        $asset->accounts()->detach($accountId);

        return response()->noContent();
    }
}
