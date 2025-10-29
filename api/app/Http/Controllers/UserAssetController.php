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
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $assets = Auth::user()->allRequesterAssets()
            ->paginate($pagination);
        return new AssetCollection($assets);
    }
}
