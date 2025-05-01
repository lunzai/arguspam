<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class AssetAccountFilter extends QueryFilter
{
    protected array $sortable = [
        'asset_id',
        'name',
        'vault_path',
        'is_default',
        'created_at',
        'updated_at',
    ];

    public function assetId(string $value): Builder
    {
        return $this->filterEqualOrIn('asset_id', $value);
    }

    public function name(string $value): Builder
    {
        return $this->filterLike('name', $value);
    }

    public function vaultPath(string $value): Builder
    {
        return $this->filterLike('vault_path', $value);
    }

    public function isDefault(string $value): Builder
    {
        return $this->filterEqual('is_default', $value);
    }

    public function createdAt(string $value): Builder
    {
        return $this->filterTimestamp('created_at', $value);
    }

    public function updatedAt(string $value): Builder
    {
        return $this->filterTimestamp('updated_at', $value);
    }
}
