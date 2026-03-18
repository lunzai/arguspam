<?php

namespace Lunzai\QuerySearch\Support;

use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Builder;
use Lunzai\QuerySearch\Contracts\FilterHandler;
use Lunzai\QuerySearch\Filters\BooleanFilter;
use Lunzai\QuerySearch\Filters\ExactFilter;
use Lunzai\QuerySearch\Filters\InFilter;
use Lunzai\QuerySearch\Filters\LikeFilter;
use Lunzai\QuerySearch\Filters\RangeFilter;
use RuntimeException;

class FilterResolver
{
    private const OPERATOR_MAP = [
        'exact' => ExactFilter::class,
        'in' => InFilter::class,
        'like' => LikeFilter::class,
        'range' => RangeFilter::class,
        'boolean' => BooleanFilter::class,
    ];

    public function __construct(private readonly Container $container) {}

    /**
     * Apply a single filter definition to the query.
     *
     * @param  array<string, mixed>  $definition
     */
    public function apply(Builder $query, string $value, array $definition): Builder
    {
        $operator = $definition['operator'] ?? 'exact';
        $column = $definition['column'] ?? '';
        $relation = $definition['relation'] ?? null;

        if ($operator === 'custom') {
            return $this->applyCustom($query, $value, $definition);
        }

        $filterClass = self::OPERATOR_MAP[$operator]
            ?? throw new RuntimeException("Unknown filter operator: [{$operator}]");

        $filter = $this->container->make($filterClass);

        if ($relation !== null) {
            // Probe the filter on an isolated query to check if it would add any
            // constraints. If the value is effectively empty (e.g. ",,,") the inner
            // filter is a no-op — applying whereHas anyway would incorrectly restrict
            // results to records that have at least one related row.
            $probe = $filter->apply($query->getModel()->newQuery(), $column, $value);

            if (empty($probe->getQuery()->wheres)) {
                return $query;
            }

            return $query->whereHas(
                $relation,
                fn (Builder $q) => $filter->apply($q, $column, $value)
            );
        }

        return $filter->apply($query, $column, $value);
    }

    /**
     * @param  array<string, mixed>  $definition
     */
    private function applyCustom(Builder $query, string $value, array $definition): Builder
    {
        $handlerClass = $definition['handler']
            ?? throw new RuntimeException('Custom filter missing [handler] key.');

        $handler = $this->container->make($handlerClass);

        if (!$handler instanceof FilterHandler) {
            throw new RuntimeException(
                "Custom filter handler [{$handlerClass}] must implement ".FilterHandler::class
            );
        }

        return $handler->handle($query, $value, $definition);
    }
}
