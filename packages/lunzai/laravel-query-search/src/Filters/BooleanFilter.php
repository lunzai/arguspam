<?php

namespace Lunzai\QuerySearch\Filters;

use Illuminate\Database\Eloquent\Builder;

class BooleanFilter
{
    private const TRUTHY = ['true', '1', 'on', 'yes'];
    private const FALSY = ['false', '0', 'off', 'no'];

    public function apply(Builder $query, string $column, string $value): Builder
    {
        $lower = strtolower(trim($value));

        if (in_array($lower, self::TRUTHY, true)) {
            return $query->where($column, true);
        }

        if (in_array($lower, self::FALSY, true)) {
            return $query->where($column, false);
        }

        // Unrecognized value — skip.
        return $query;
    }
}
