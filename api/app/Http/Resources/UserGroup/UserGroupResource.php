<?php

namespace App\Http\Resources\UserGroup;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\AssetAccessGrant\AssetAccessGrantResource;
use App\Http\Resources\Org\OrgResource;

class UserGroupResource extends JsonResource
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
                'name' => $this->name,
                'description' => $this->description,
                'status' => $this->status,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            $this->mergeWhen(count($this->resource->getRelations()) > 0, [
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
