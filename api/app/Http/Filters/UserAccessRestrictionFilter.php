<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class UserAccessRestrictionFilter extends QueryFilter
{
    protected array $sortable = [
        'user_id',
        'type',
        'status',
        'created_at',
        'updated_at',
    ];

    public function userId(string $value): Builder
    {
        return $this->filterEqualOrIn('user_id', $value);
    }

    public function type(string $value): Builder
    {
        return $this->filterEqualOrIn('type', $value);
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
}
