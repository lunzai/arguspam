<?php

namespace App\Http\Controllers;

use App\Http\Resources\Asset\AssetCollection;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserAssetController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewRequestable', Asset::class);
        $pagination = $request->input('per_page', config('pam.pagination.per_page'));
        $assets = Auth::user()->allRequesterAssets()
            ->paginate($pagination);
        return new AssetCollection($assets);
    }

    public function show(Asset $asset)
    {
        $this->authorize('viewRequestable', Asset::class);
        $canRequest = Auth::user()->canRequestAsset(Auth::user(), $asset);

        if (!$canRequest) {
            return $this->unauthorized('You are not authorized to request access to this asset');
        }
        return $this->ok();
    }
}
