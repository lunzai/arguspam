<?php

namespace App\Http\Resources\OrgAiProvider;

use App\Http\Resources\Collection;
use Illuminate\Http\Request;

class OrgAiProviderCollection extends Collection
{
    /**
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
