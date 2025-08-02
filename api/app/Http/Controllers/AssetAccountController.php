<?php

namespace App\Http\Controllers;

use App\Http\Requests\Asset\UpdateAssetCredentialRequest;
use App\Http\Resources\Asset\AssetResource;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;

class AssetAccountController extends Controller
{
    public function update(UpdateAssetCredentialRequest $request, Asset $asset): AssetResource
    {
        $this->authorize('update', $asset);
        $validated = $request->validated();
        DB::transaction(function () use ($validated, $asset) {
            $asset->update($validated);
            if (isset($validated['username']) && isset($validated['password'])) {
                $asset->adminAccount()->update([
                    'username' => $validated['username'],
                    'password' => $validated['password'],
                ]);
            }
        });
        $asset->refresh();
        return new AssetResource($asset);
    }
}
