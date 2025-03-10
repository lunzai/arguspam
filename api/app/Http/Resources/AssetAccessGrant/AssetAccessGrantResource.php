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
                'assetId' => $this->asset_id,
                'userId' => $this->user_id,
                'userGroupId' => $this->user_group_id,
                'role' => $this->role,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'asset' => AssetResource::collection(
                        $this->whenLoaded('asset')
                    ),
                    'user' => UserResource::collection(
                        $this->whenLoaded('user')
                    ),
                    'userGroup' => UserGroupResource::collection(
                        $this->whenLoaded('userGroup')
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
