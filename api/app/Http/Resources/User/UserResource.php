<?php

namespace App\Http\Resources\User;

use App\Http\Resources\Org\OrgResource;
use App\Http\Resources\UserGroup\UserGroupResource;
use App\Http\Resources\AssetAccessGrant\AssetAccessGrantResource;
use App\Http\Resources\Request\RequestResource;
use App\Http\Resources\Session\SessionResource;
use App\Http\Resources\UserAccessRestriction\UserAccessRestrictionResource;
use App\Http\Resources\ActionAudit\ActionAuditResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
                    'emailVerifiedAt' => $this->email_verified_at,
                    'twoFactorEnabled' => $this->two_factor_enabled,
                    'twoFactorConfirmedAt' => $this->two_factor_confirmed_at,
                    'lastLoginAt' => $this->last_login_at,
                    'status' => $this->status,
                    'createdAt' => $this->created_at,
                    'updatedAt' => $this->updated_at,
                ]),
            ],
            $this->mergeWhen(count($this->resource->getRelations()) > 0, [
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
                ],
            ]),
        ];
    }
}
