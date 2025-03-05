<?php

namespace App\Http\Filters;

use App\Traits\IncludeRelationships;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QueryFilter
{
    use IncludeRelationships;

    protected Builder $builder;
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function filter(array $arr): Builder
    {
        foreach ($arr as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }

        return $this->builder;
    }

    public function sort(string $value): Builder
    {
        $arr = explode(',', $value);
        foreach ($arr as $key => $value) {
            if ($value[0] === '-') {
                $this->builder->orderBy(substr($key, 1), 'desc');
            } else {
                $this->builder->orderBy($key, 'asc');
            }
        }

        return $this->builder;
    }

    public function include(string $value): Builder
    {
        $relations = explode(',', $value);
        $modelClass = get_class($this->builder->getModel());

        foreach ($relations as $relation) {
            $relation = trim($relation);
            if ($this->isValidRelation($modelClass, $relation)) {
                $this->builder->with($relation);
            }
        }

        return $this->builder;
    }

    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;
        foreach ($this->request->all() as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }

        return $this->builder;
    }
}
