<?php

namespace App\Http\Resources\AssetAccessGrant;

use App\Http\Resources\Asset\AssetResource;
use App\Http\Resources\Resource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\UserGroup\UserGroupResource;
use Illuminate\Http\Request;

class AssetAccessGrantResource extends Resource
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
                'asset_id' => $this->asset_id,
                'user_id' => $this->user_id,
                'user_group_id' => $this->user_group_id,
                'role' => $this->role,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'asset' => AssetResource::make(
                        $this->whenLoaded('asset')
                    ),
                    'user' => UserResource::make(
                        $this->whenLoaded('user')
                    ),
                    'userGroup' => UserGroupResource::make(
                        $this->whenLoaded('userGroup')
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
