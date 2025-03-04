<?php

namespace App\Http\Resources\ActionAudit;

use App\Http\Resources\Collection;
use Illuminate\Http\Request;

class ActionAuditCollection extends Collection
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
