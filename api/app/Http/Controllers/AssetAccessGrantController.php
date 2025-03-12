<?php

namespace App\Http\Controllers;

use App\Http\Resources\AssetAccessGrant\AssetAccessGrantCollection;
use App\Models\Asset;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

// TODO: REWRITE
class AssetAccessGrantController extends Controller
{
    use IncludeRelationships;

    public function index(Asset $asset): AssetAccessGrantCollection
    {
        $grants = $asset->accessGrants()->paginate(config('pam.pagination.per_page'));

        return new AssetAccessGrantCollection($grants);
    }

    public function store(Request $request, Asset $asset): Response
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'string'], // You might want to add enum validation here
        ]);

        $asset->accessGrants()->create($validated);

        return response()->noContent(Response::HTTP_CREATED);
    }

    public function update(Request $request, Asset $asset, string $grantId): Response
    {
        $validated = $request->validate([
            'role' => ['required', 'string'], // You might want to add enum validation here
        ]);

        $asset->accessGrants()->findOrFail($grantId)->update($validated);

        return response()->noContent();
    }

    public function destroy(Asset $asset, string $grantId): Response
    {
        $asset->accessGrants()->findOrFail($grantId)->delete();

        return response()->noContent();
    }
}
