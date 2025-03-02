<?php

namespace App\Http\Resources\ActionAudit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActionAuditResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
