<?php

namespace App\Models;

use App\Http\Filters\QueryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\Gate;

class Model extends EloquentModel
{
    public $statusColumn = 'status';
    /**
     * List of relationships that can be included via API
     *
     * @var array
     */
    public static $includable = [];

    public function scopeFilter(Builder $query, QueryFilter $filter): Builder
    {
        return $filter->apply($query);
    }

    public function scopeVisibleTo(Builder $query, User $user, string $column = 'user_id'): Builder
    {
        if (Gate::allows('viewAny', $this)) {
            return $query;
        }

        return $query->where($column, $user->id);
    }
}
