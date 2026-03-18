<?php

namespace Lunzai\QuerySearch\Filters;

use Illuminate\Database\Eloquent\Builder;

class RangeFilter
{
    public function apply(Builder $query, string $column, string $value): Builder
    {
        if (!str_contains($value, ',')) {
            // No comma present — not a valid range format; skip.
            return $query;
        }

        $parts = explode(',', $value, 2);
        $min = trim($parts[0]);
        $max = trim($parts[1]);

        if ($min !== '' && $max !== '') {
            return $query->whereBetween($column, [$min, $max]);
        }

        if ($min !== '') {
            return $query->where($column, '>=', $min);
        }

        if ($max !== '') {
            return $query->where($column, '<=', $max);
        }

        // Both sides empty — skip.
        return $query;
    }
}
