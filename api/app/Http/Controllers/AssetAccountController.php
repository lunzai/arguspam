<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssetAccount\StoreAssetAccountRequest;
use App\Models\Asset;
use App\Models\AssetAccount;
use Illuminate\Http\Response;

class AssetAccountController extends Controller
{
    public function store(StoreAssetAccountRequest $request, Asset $asset): Response
    {
        $this->authorize('create', AssetAccount::class);
        $validated = $request->validated();

        $asset->accounts()->attach($validated['account_ids']);

        return $this->created();
    }

    public function destroy(Asset $asset, string $accountId): Response
    {
        $this->authorize('delete', AssetAccount::class);
        $asset->accounts()->detach($accountId);

        return $this->noContent();
    }
}
