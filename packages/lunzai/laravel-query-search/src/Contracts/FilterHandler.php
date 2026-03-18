<?php

namespace Lunzai\QuerySearch\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface FilterHandler
{
    /**
     * Apply a custom filter to the query.
     *
     * @param  array<string, mixed>  $definition  The filter definition from filters()
     */
    public function handle(Builder $query, string $value, array $definition): Builder;
}
