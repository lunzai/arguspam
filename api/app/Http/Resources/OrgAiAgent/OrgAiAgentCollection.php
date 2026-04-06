<?php

namespace App\Http\Resources\OrgAiAgent;

use App\Http\Resources\Collection;
use Illuminate\Http\Request;

class OrgAiAgentCollection extends Collection
{
    /**
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
