<?php

namespace Lunzai\QuerySearch\Filters;

use Illuminate\Database\Eloquent\Builder;

class LikeFilter
{
    public function apply(Builder $query, string $column, string $value): Builder
    {
        if (trim($value) === '') {
            return $query;
        }

        $escaped = addcslashes($value, '\\%_');

        return $query->where($column, 'like', '%'.$escaped.'%');
    }
}
