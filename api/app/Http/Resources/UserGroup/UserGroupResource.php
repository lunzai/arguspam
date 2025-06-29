<?php

namespace App\Http\Resources\UserGroup;

use App\Http\Resources\AssetAccessGrant\AssetAccessGrantResource;
use App\Http\Resources\Org\OrgResource;
use App\Http\Resources\Resource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;

class UserGroupResource extends Resource
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
                'name' => $this->name,
                'description' => $this->description,
                'status' => $this->status,
                'users_count' => $this->whenCounted('users'),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'users' => UserResource::collection(
                        $this->whenLoaded('users')
                    ),
                    'assetAccessGrants' => AssetAccessGrantResource::collection(
                        $this->whenLoaded('assetAccessGrants')
                    ),
                    'org' => OrgResource::collection(
                        $this->whenLoaded('org')
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
