<?php

namespace App\Http\Resources\AssetAccount;

use Illuminate\Http\Request;
use App\Http\Resources\Collection;

class AssetAccountCollection extends Collection
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
