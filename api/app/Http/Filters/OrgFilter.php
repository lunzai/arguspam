<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;

class OrgFilter extends QueryFilter
{
    protected array $sortable = [
        'name',
        'description',
        'status',
        'created_at',
        'updated_at',
    ];

    public function name(string $value): Builder
    {
        return $this->filterLike('name', $value);
    }

    public function description(string $value): Builder
    {
        return $this->filterLike('description', $value);
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
