<?php

namespace App\Http\Resources\ActionAudit;

use App\Http\Resources\Org\OrgResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;

class ActionAuditResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'attributes' => [
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
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'org' => OrgResource::collection(
                        $this->whenLoaded('org')
                    ),
                    'user' => UserResource::collection(
                        $this->whenLoaded('user')
                    ),
                    'createdBy' => UserResource::collection(
                        $this->whenLoaded('createdBy')
                    ),
                ],
            ]),
        ];
    }
}
