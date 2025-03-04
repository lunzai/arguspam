<?php

namespace App\Http\Resources\Asset;

use App\Http\Resources\Collection;
use Illuminate\Http\Request;

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
