<?php

namespace App\Http\Resources\Request;

use App\Http\Resources\Asset\AssetResource;
use App\Http\Resources\AssetAccount\AssetAccountResource;
use App\Http\Resources\Org\OrgResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;

class RequestResource extends Resource
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
                'assetId' => $this->asset_id,
                'assetAccountId' => $this->asset_account_id,
                'requesterId' => $this->requester_id,
                'startDatetime' => $this->start_datetime,
                'endDatetime' => $this->end_datetime,
                'duration' => $this->duration,
                'reason' => $this->reason,
                'intendedQuery' => $this->intended_query,
                'scope' => $this->scope,
                'isAccessSensitiveData' => $this->is_access_sensitive_data,
                'sensitiveDataNote' => $this->sensitive_data_note,
                'approverNote' => $this->approver_note,
                'approverRiskRating' => $this->approver_risk_rating,
                'aiNote' => $this->ai_note,
                'aiRiskRating' => $this->ai_risk_rating,
                'status' => $this->status,
                'approvedBy' => $this->approved_by,
                'approvedAt' => $this->approved_at,
                'rejectedBy' => $this->rejected_by,
                'rejectedAt' => $this->rejected_at,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'org' => OrgResource::collection(
                        $this->whenLoaded('org')
                    ),
                    'asset' => AssetResource::collection(
                        $this->whenLoaded('asset')
                    ),
                    'assetAccount' => AssetAccountResource::collection(
                        $this->whenLoaded('assetAccount')
                    ),
                    'requester' => UserResource::collection(
                        $this->whenLoaded('requester')
                    ),
                    'approver' => UserResource::collection(
                        $this->whenLoaded('approver')
                    ),
                    'approvedBy' => UserResource::collection(
                        $this->whenLoaded('approvedBy')
                    ),
                    'rejectedBy' => UserResource::collection(
                        $this->whenLoaded('rejectedBy')
                    ),
                ],
            ]),
        ];
    }
}
