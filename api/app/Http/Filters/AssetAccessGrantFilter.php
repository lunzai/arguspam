<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class AssetAccessGrantFilter extends QueryFilter
{
    protected array $sortable = [
        'asset_id',
        'user_id',
        'user_group_id',
        'role',
        'created_at',
        'updated_at',
    ];

    public function assetId(string $value): Builder
    {
        return $this->filterEqualOrIn('asset_id', $value);
    }

    public function userId(string $value): Builder
    {
        return $this->filterEqualOrIn('user_id', $value);
    }

    public function userGroupId(string $value): Builder
    {
        return $this->filterEqualOrIn('user_group_id', $value);
    }

    public function role(string $value): Builder
    {
        return $this->filterEqualOrIn('role', $value);
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
