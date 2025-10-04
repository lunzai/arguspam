<?php

namespace App\Http\Resources\Org;

use App\Http\Resources\Resource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\UserGroup\UserGroupResource;
use Illuminate\Http\Request;

class OrgResource extends Resource
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
                'name' => e($this->name),
                'description' => e($this->description),
                'status' => $this->status,
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
                    'createdBy' => UserResource::make(
                        $this->whenLoaded('createdBy')
                    ),
                    'updatedBy' => UserResource::make(
                        $this->whenLoaded('updatedBy')
                    ),
                ],
            ]),
        ];
    }
}
