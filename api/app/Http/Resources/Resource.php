<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class Resource extends JsonResource
{
    protected function hasRelation(): bool
    {
        $relations = $this->resource->getRelations();
        unset($relations['pivot']);

        return count($relations) > 0;
    }
}
