<?php

namespace App\Http\Resources\User;

use App\Http\Resources\AssetAccessGrant\AssetAccessGrantResource;
use App\Http\Resources\Org\OrgResource;
use App\Http\Resources\Role\RoleResource;
use App\Http\Resources\Request\RequestResource;
use App\Http\Resources\Resource;
use App\Http\Resources\Session\SessionResource;
use App\Http\Resources\UserAccessRestriction\UserAccessRestrictionResource;
use App\Http\Resources\UserGroup\UserGroupResource;
use Illuminate\Http\Request;

class UserResource extends Resource
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
                'email' => $this->email,
                $this->mergeWhen($request->routeIs('users.*'), [
                    'email_verified_at' => $this->email_verified_at,
                    'two_factor_enabled' => $this->two_factor_enabled,
                    'two_factor_confirmed_at' => $this->two_factor_confirmed_at,
                    'last_login_at' => $this->last_login_at,
                    'status' => $this->status,
                    'created_at' => $this->created_at,
                    'updated_at' => $this->updated_at,
                ]),
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'orgs' => OrgResource::collection(
                        $this->whenLoaded('orgs')
                    ),
                    'userGroups' => UserGroupResource::collection(
                        $this->whenLoaded('userGroups')
                    ),
                    'assetAccessGrants' => AssetAccessGrantResource::collection(
                        $this->whenLoaded('assetAccessGrants')
                    ),
                    'approverAssetAccessGrants' => AssetAccessGrantResource::collection(
                        $this->whenLoaded('approverAssetAccessGrants')
                    ),
                    'requesterAssetAccessGrants' => AssetAccessGrantResource::collection(
                        $this->whenLoaded('requesterAssetAccessGrants')
                    ),
                    'requests' => RequestResource::collection(
                        $this->whenLoaded('requests')
                    ),
                    'sessions' => SessionResource::collection(
                        $this->whenLoaded('sessions')
                    ),
                    'accessRestrictions' => UserAccessRestrictionResource::collection(
                        $this->whenLoaded('accessRestrictions')
                    ),
                    'roles' => RoleResource::collection(
                        $this->whenLoaded('roles')
                    ),
                ],
            ]),
        ];
    }
}
