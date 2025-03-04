<?php

namespace App\Http\Resources\Org;

use Illuminate\Http\Request;
use App\Http\Resources\Collection;
use App\Http\Resources\Org\OrgResource;

class OrgCollection extends Collection
{
    public $collects = OrgResource::class;

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
