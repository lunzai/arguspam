<?php

namespace App\Http\Resources\AccessRestriction;

use App\Http\Resources\Resource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\UserGroup\UserGroupResource;
use Illuminate\Http\Request;

class AccessRestrictionResource extends Resource
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
                'name' => $this->name,
                'description' => $this->description,
                'type' => $this->type,
                'data' => $this->data,
                'status' => $this->status,
                'weight' => $this->weight,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'users' => UserResource::collection(
                        $this->whenLoaded('users')
                    ),
                    'userGroups' => UserGroupResource::collection(
                        $this->whenLoaded('userGroups')
                    ),
                    'createdBy' => UserResource::collection(
                        $this->whenLoaded('createdBy')
                    ),
                    'updatedBy' => UserResource::collection(
                        $this->whenLoaded('updatedBy')
                    ),
                ],
            ]),
        ];
    }
}
