<?php

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class QueryFilter
{
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

    /**
     * Check if a relation path is valid by recursively validating each segment
     *
     * @param  string  $modelClass  The starting model class
     * @param  string  $relationPath  The dot-notation relation path (e.g., 'posts.comments.author')
     * @return bool Whether the relation path is valid
     */
    protected function isValidRelation(string $modelClass, string $relationPath): bool
    {
        $segments = explode('.', $relationPath);
        $currentModel = $modelClass;
        $validPath = true;

        foreach ($segments as $segment) {
            // Check if current model has expandable property
            if (property_exists($currentModel, 'includable')) {
                $allowedRelations = $currentModel::includable;

                // If this segment is not in allowed relations, path is invalid
                if (!in_array($segment, $allowedRelations)) {
                    $validPath = false;
                    break;
                }
            }

            // Get the related model for the next iteration
            if (method_exists($currentModel, $segment)) {
                $relationInstance = (new $currentModel)->$segment();
                $relatedModelClass = get_class($relationInstance->getRelated());
                $currentModel = $relatedModelClass;
            } else {
                // If relation method doesn't exist, path is invalid
                $validPath = false;
                break;
            }
        }

        return $validPath;
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
