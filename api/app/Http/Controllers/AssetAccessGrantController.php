<?php

namespace App\Http\Controllers;

use App\Enums\AssetAccessRole;
use App\Http\Requests\AssetAccessGrant\StoreAssetAccessGrantRequest;
use App\Models\Asset;
use App\Models\AssetAccessGrant;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;

// TODO: REWRITE
class AssetAccessGrantController extends Controller
{
    use IncludeRelationships;

    public function store(StoreAssetAccessGrantRequest $request, Asset $asset): Response
    {
        $this->authorize('create', AssetAccessGrant::class);
        $validated = $request->validated();
        $data = array_merge(
            array_map(function ($item) use ($validated, $asset) {
                return [
                    'user_id' => $item,
                    'user_group_id' => null,
                    'asset_id' => $asset->id,
                    'role' => $validated['role'],
                ];
            }, $validated['user_ids'] ?? []),
            array_map(function ($item) use ($validated, $asset) {
                return [
                    'user_id' => null,
                    'user_group_id' => $item,
                    'asset_id' => $asset->id,
                    'role' => $validated['role'],
                ];
            }, $validated['user_group_ids'] ?? []),
        );
        if (empty($data)) {
            return $this->noContent();
        }
        $asset->accessGrants()
            ->createMany($data);
        return $this->created();
    }

    public function destroy(Request $request, Asset $asset): Response
    {
        $this->authorize('delete', AssetAccessGrant::class);
        $validated = $request->validate([
            'role' => ['required', new Enum(AssetAccessRole::class)],
            'type' => ['required', 'string', 'in:user,user_group'],
            'user_id' => ['required_if:type,user', 'integer', 'exists:users,id'],
            'user_group_id' => ['required_if:type,user_group', 'integer', 'exists:user_groups,id'],
        ]);
        $query = $asset->accessGrants()
            ->where('role', $validated['role']);
        if ($validated['type'] === 'user') {
            $query->where('user_id', $validated['user_id']);
        } else {
            $query->where('user_group_id', $validated['user_group_id']);
        }
        $query->delete();
        return $this->noContent();
    }

    // public function index(Asset $asset, Request $request): AssetAccessGrantCollection
    // {
    //     $this->authorize('viewAny', AssetAccessGrant::class);
    //     $pagination = $request->get('per_page', config('pam.pagination.per_page'));
    //     // $grants = Cache::remember(
    //     //     CacheKey::ASSET_ACCESS_GRANTS->value,
    //     //     config('cache.default_ttl'),
    //     //     function () use ($asset, $pagination) {
    //     //         return $asset->accessGrants()->paginate($pagination);
    //     //     }
    //     // );
    //     $grants = $asset->accessGrants()
    //         ->paginate($pagination);
    //     return new AssetAccessGrantCollection($grants);
    // }

    // public function store(Request $request, Asset $asset): Response
    // {
    //     $this->authorize('create', AssetAccessGrant::class);
    //     $validated = $request->validate([
    //         'user_id' => ['required', 'exists:users,id'],
    //         'role' => ['required', 'string'], // You might want to add enum validation here
    //     ]);

    //     $asset->accessGrants()->create($validated);

    //     return $this->created();
    // }

    // public function update(Request $request, Asset $asset, string $grantId): Response
    // {
    //     $this->authorize('update', AssetAccessGrant::class);
    //     $validated = $request->validate([
    //         'role' => ['required', 'string'], // You might want to add enum validation here
    //     ]);

    //     $asset->accessGrants()->findOrFail($grantId)->update($validated);

    //     return $this->noContent();
    // }

    // public function destroy(Asset $asset, string $grantId): Response
    // {
    //     $this->authorize('delete', AssetAccessGrant::class);
    //     $asset->accessGrants()->findOrFail($grantId)->delete();

    //     return $this->noContent();
    // }
}
