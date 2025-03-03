<?php

namespace App\Http\Resources\Asset;

use Illuminate\Http\Request;
use App\Http\Resources\Collection;

class AssetCollection extends Collection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
