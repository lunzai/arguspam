<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class UserFilter extends QueryFilter
{
    protected array $sortable = [
        'name',
        'email',
        'status',
        'last_login_at',
        'created_at',
        'updated_at',
    ];

    public function orgId(string $value): Builder
    {
        return $this->filterEqualOrIn('org_id', $value);
    }

    public function name(string $value): Builder
    {
        return $this->filterLike('name', $value);
    }

    public function email(string $value): Builder
    {
        return $this->filterLike('email', $value);
    }

    public function status(string $value): Builder
    {
        return $this->filterEqualOrIn('status', $value);
    }

    public function createdAt(string $value): Builder
    {
        return $this->filterTimestamp('created_at', $value);
    }

    public function updatedAt(string $value): Builder
    {
        return $this->filterTimestamp('updated_at', $value);
    }

    public function lastLoginAt(string $value): Builder
    {
        return $this->filterTimestamp('last_login_at', $value);
    }

    public function twoFactorEnabled(string $value): Builder
    {
        return $this->filterEqual('two_factor_enabled', $value);
    }
}
