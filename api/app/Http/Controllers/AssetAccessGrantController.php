<?php

namespace App\Http\Controllers;

use App\Enums\AssetAccessRole;
use App\Http\Requests\AssetAccessGrant\StoreAssetAccessGrantRequest;
use App\Models\Asset;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rules\Enum;

class AssetAccessGrantController extends Controller
{
    use IncludeRelationships;

    public function store(StoreAssetAccessGrantRequest $request, Asset $asset): Response
    {
        $this->authorize('addAccessGrant', $asset);
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
        $this->authorize('removeAccessGrant', $asset);
        $validated = $request->validate([
            'role' => ['required', new Enum(AssetAccessRole::class)],
            'type' => ['required', 'string', 'in:user,user_group'],
            'user_id' => ['nullable', 'required_if:type,user', 'integer', 'exists:users,id'],
            'user_group_id' => ['nullable', 'required_if:type,user_group', 'integer', 'exists:user_groups,id'],
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
}
