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

    protected function selectedFields($request, $availableFields)
    {
        $selectedFields = $request->get('fields');
        if (!$selectedFields) {
            return $availableFields;
        }
        $requestedFields = explode(',', $selectedFields);
        return array_intersect($availableFields, array_flip($requestedFields));
    }
}
