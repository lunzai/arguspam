<?php

namespace App\Http\Controllers;

use App\Http\Requests\Asset\UpdateAssetCredentialRequest;
use App\Http\Resources\Asset\AssetResource;
use App\Models\Asset;
use Illuminate\Support\Facades\DB;

class AssetAccountController extends Controller
{
    /**
     * Update admin account credentials
     */
    public function update(UpdateAssetCredentialRequest $request, Asset $asset): AssetResource
    {
        $this->authorize('updateAdminAccount', $asset);
        $validated = $request->validated();
        DB::transaction(function () use ($validated, $asset) {
            $asset->update($validated);
            if (isset($validated['username']) && isset($validated['password'])) {
                $adminAccount = $asset->adminAccount;
                $adminAccount->username = $validated['username'];
                $adminAccount->password = $validated['password'];
                $adminAccount->save();
            }
        });
        $asset->refresh();
        return new AssetResource($asset);
    }
}
