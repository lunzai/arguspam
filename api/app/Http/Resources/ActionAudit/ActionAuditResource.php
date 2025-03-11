<?php

namespace App\Http\Resources\ActionAudit;

use App\Http\Resources\Org\OrgResource;
use App\Http\Resources\Resource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;

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
                'org_id' => $this->org_id,
                'user_id' => $this->user_id,
                'action_type' => $this->action_type,
                'entity_type' => $this->entity_type,
                'entity_id' => $this->entity_id,
                'description' => $this->description,
                'previous_state' => $this->previous_state,
                'new_state' => $this->new_state,
                'ip_address' => $this->ip_address,
                'user_agent' => $this->user_agent,
                'additional_data' => $this->additional_data,
                'created_at' => $this->created_at,
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
