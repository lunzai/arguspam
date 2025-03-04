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
        return [
            'id' => $this->id,
            'orgId' => $this->org_id,
            'userId' => $this->user_id,
            'actionType' => $this->action_type,
            'entityType' => $this->entity_type,
            'entityId' => $this->entity_id,
            'description' => $this->description,
            'previousState' => $this->previous_state,
            'newState' => $this->new_state,
            'ipAddress' => $this->ip_address,
            'userAgent' => $this->user_agent,
            'additionalData' => $this->additional_data,
            'createdAt' => $this->created_at,
        ];
    }
}
