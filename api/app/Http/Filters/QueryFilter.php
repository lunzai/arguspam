<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

abstract class QueryFilter
{
    protected Builder $builder;
    protected Request $request;
    protected array $sortable = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;
        foreach ($this->request->all() as $key => $value) {
            if (method_exists($this, $key) && $value !== null) {
                $this->$key($value);
            }
        }
        return $this->builder;
    }

    public function count(string $value): Builder
    {
        $value = explode(',', $value);
        $value = array_filter($value, fn ($v) => $v !== '');
        return $this->builder->withCount($value);
    }

    public function filter(array $arr): Builder
    {
        foreach ($arr as $key => $value) {
            $method = Str::camel($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this->builder;
    }

    public function sort(string $value): Builder
    {
        $arr = explode(',', $value);
        foreach ($arr as $sort) {
            $isDesc = str_starts_with($sort, '-');
            $field = $isDesc ? substr($sort, 1) : $sort;
            if (!in_array($field, $this->sortable)) {
                continue;
            }
            $this->builder->orderBy($field, $isDesc ? 'desc' : 'asc');
            // TODO: Sort by relation
        }
        return $this->builder;
    }

    public function include(string $value): Builder
    {
        $relations = explode(',', $value);

        foreach ($relations as $relation) {
            if ($this->isValidRelation($relation)) {
                $this->builder->with($relation);
            }
        }
        return $this->builder;
    }

    protected function isValidRelation(string $relation): bool
    {
        return method_exists($this->builder->getModel(), Str::camel($relation));
    }

    protected function filterTimestamp(string $field, string $value): Builder
    {
        $arr = explode(',', $value);
        if (count($arr) > 1) {
            return $this->builder->whereBetween($field, [$arr[0], $arr[1]]);
        }
        if ($value[0] === '-') {
            return $this->builder->where($field, '<=', substr($value, 1));
        }
        return $this->builder->where($field, '>=', $value);
    }

    protected function filterLike(string $field, string $value): Builder
    {
        return $this->builder->where($field, 'like', '%'.$value.'%');
    }

    protected function filterEqualOrIn(string $field, string $value): Builder
    {
        $arr = explode(',', $value);
        if (count($arr) === 1) {
            return $this->builder->where($field, $arr[0]);
        }
        return $this->builder->whereIn($field, $arr);
    }

    protected function filterEqual(string $field, string $value): Builder
    {
        return $this->builder->where($field, $value);
    }

    protected function filterBetween(string $field, string $value): Builder
    {
        $arr = explode(',', $value);
        return $this->builder->whereBetween($field, [$arr[0], $arr[1]]);
    }
}
