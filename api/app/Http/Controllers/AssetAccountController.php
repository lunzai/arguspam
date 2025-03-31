<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssetAccount\StoreAssetAccountRequest;
use App\Models\Asset;
use Illuminate\Http\Response;

class AssetAccountController extends Controller
{
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
