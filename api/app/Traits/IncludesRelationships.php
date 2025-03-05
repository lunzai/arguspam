<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait IncludesRelationships
{
    /**
     * Apply includes to a query based on request parameters
     */
    protected function applyIncludes(Builder $query, Request $request): Builder
    {
        if ($request->has('include')) {
            $includes = explode(',', $request->input('include'));
            $modelClass = get_class($query->getModel());

            foreach ($includes as $relation) {
                $relation = trim($relation);
                if ($this->isValidRelation($modelClass, $relation)) {
                    $query->with($relation);
                }
            }
        }

        return $query;
    }

    /**
     * Check if a relation path is valid by recursively validating each segment
     *
     * @param  string  $modelClass  The starting model class
     * @param  string  $relationPath  The dot-notation relation path
     * @return bool Whether the relation path is valid
     */
    protected function isValidRelation(string $modelClass, string $relationPath): bool
    {
        $segments = explode('.', $relationPath);
        $currentModel = $modelClass;
        $validPath = true;

        foreach ($segments as $segment) {
            // Check if current model has includable property
            if (property_exists($currentModel, 'includable')) {
                $allowedRelations = $currentModel::$includable;

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
}
