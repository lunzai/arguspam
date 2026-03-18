<?php

namespace Lunzai\QuerySearch\Filters;

use Illuminate\Database\Eloquent\Builder;

class ExactFilter
{
    public function apply(Builder $query, string $column, string $value): Builder
    {
        if (trim($value) === '') {
            return $query;
        }

        return $query->where($column, $value);
    }
}
