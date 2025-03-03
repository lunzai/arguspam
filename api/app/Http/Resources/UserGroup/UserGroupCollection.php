<?php

namespace App\Http\Resources\UserGroup;

use Illuminate\Http\Request;
use App\Http\Resources\Collection;

class UserGroupCollection extends Collection
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
