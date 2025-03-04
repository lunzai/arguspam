<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

trait IsExpandable
{
    private $expandableParam = 'expand';

    /**
     * Check if request has expandable parameter.
     */
    protected function hasExpandable(): bool
    {
        return request()->has($this->expandableParam);
    }

    /**
     * Get requested expandable relationships.
     */
    protected function getRequestedExpands(): array
    {
        if (!$this->hasExpandable()) {
            return [];
        }

        return explode(',', request()->get($this->expandableParam));
    }

    /**
     * Filter requested expands to only allowed ones.
     *
     * @param  Model|string  $modelClass
     */
    protected function getAllowedExpands($modelClass): array
    {
        $requested = $this->getRequestedExpands();
        // Get expandable fields from model
        $model = is_string($modelClass) ? new $modelClass() : $modelClass;
        $allowed = method_exists($model, 'getExpandable')
            ? $model->getExpandable()
            : [];
        Log::info('requested', ['requested' => $requested]);
        Log::info('allowed', ['allowed' => $allowed]);

        return array_intersect($requested, $allowed);
    }

    /**
     * Apply expands to query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyExpands($query)
    {
        $modelClass = get_class($query->getModel());
        $expands = $this->getAllowedExpands($modelClass);
        Log::info('applyExpands', ['expands' => $expands]);
        if (!empty($expands)) {
            $query->with($expands);
        }

        return $query;
    }
}
