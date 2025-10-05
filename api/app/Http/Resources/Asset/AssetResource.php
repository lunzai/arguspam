<?php

namespace App\Http\Resources\Asset;

use App\Http\Resources\AssetAccessGrant\AssetAccessGrantResource;
use App\Http\Resources\AssetAccount\AssetAccountResource;
use App\Http\Resources\Org\OrgResource;
use App\Http\Resources\Request\RequestResource;
use App\Http\Resources\Resource;
use App\Http\Resources\Session\SessionResource;
use App\Http\Resources\User\UserResource;
use App\Http\Resources\UserGroup\UserGroupResource;
use Illuminate\Http\Request;

class AssetResource extends Resource
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
                'name' => e($this->name),
                'description' => e($this->description),
                'status' => $this->status,
                'host' => $this->host,
                'port' => $this->port,
                'dbms' => $this->dbms->label(),
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'org' => OrgResource::make(
                        $this->whenLoaded('org')
                    ),
                    'accounts' => AssetAccountResource::collection(
                        $this->whenLoaded('accounts')
                    ),
                    'accessGrants' => AssetAccessGrantResource::collection(
                        $this->whenLoaded('accessGrants')
                    ),
                    'requests' => RequestResource::collection(
                        $this->whenLoaded('requests')
                    ),
                    'sessions' => SessionResource::collection(
                        $this->whenLoaded('sessions')
                    ),
                    'users' => UserResource::collection(
                        $this->whenLoaded('users')
                    ),
                    'userGroups' => UserGroupResource::collection(
                        $this->whenLoaded('userGroups')
                    ),
                    'approverUserGroups' => UserGroupResource::collection(
                        $this->whenLoaded('approverUserGroups')
                    ),
                    'requesterUserGroups' => UserGroupResource::collection(
                        $this->whenLoaded('requesterUserGroups')
                    ),
                    'approverUsers' => UserResource::collection(
                        $this->whenLoaded('approverUsers')
                    ),
                    'requesterUsers' => UserResource::collection(
                        $this->whenLoaded('requesterUsers')
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
