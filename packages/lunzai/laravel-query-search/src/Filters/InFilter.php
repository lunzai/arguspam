<?php

namespace Lunzai\QuerySearch\Filters;

use Illuminate\Database\Eloquent\Builder;

class InFilter
{
    public function apply(Builder $query, string $column, string $value): Builder
    {
        $values = array_values(array_filter(
            array_map('trim', explode(',', $value)),
            fn (string $v) => $v !== ''
        ));

        if (empty($values)) {
            return $query;
        }

        if (count($values) === 1) {
            return $query->where($column, $values[0]);
        }

        return $query->whereIn($column, $values);
    }
}
