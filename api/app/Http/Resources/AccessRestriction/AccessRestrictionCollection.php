<?php

namespace App\Http\Resources\AccessRestriction;

use App\Http\Resources\Collection;
use Illuminate\Http\Request;

class AccessRestrictionCollection extends Collection
{
    public $collects = AccessRestrictionResource::class;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}
