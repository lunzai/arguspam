<?php

namespace App\Http\Resources\Request;

use App\Http\Resources\Collection;
use Illuminate\Http\Request;

class RequestCollection extends Collection
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
