<?php

namespace App\Http\Resources\Request;

use App\Http\Resources\Asset\AssetResource;
use App\Http\Resources\AssetAccount\AssetAccountResource;
use App\Http\Resources\Org\OrgResource;
use App\Http\Resources\Resource;
use App\Http\Resources\Session\SessionResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;

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
                'org_id' => $this->org_id,
                'asset_id' => $this->asset_id,
                'asset_account_id' => $this->asset_account_id,
                'requester_id' => $this->requester_id,
                'start_datetime' => $this->start_datetime,
                'end_datetime' => $this->end_datetime,
                'duration' => $this->duration,
                'reason' => e($this->reason),
                'intended_query' => e($this->intended_query),
                'scope' => $this->scope,
                'is_access_sensitive_data' => $this->is_access_sensitive_data,
                'sensitive_data_note' => e($this->sensitive_data_note),
                'approver_note' => e($this->approver_note),
                'approver_risk_rating' => $this->approver_risk_rating,
                'ai_note' => e($this->ai_note),
                'ai_risk_rating' => $this->ai_risk_rating,
                'status' => $this->status,
                'submitted_at' => $this->submitted_at,
                'approved_by' => $this->approved_by,
                'approved_at' => $this->approved_at,
                'rejected_by' => $this->rejected_by,
                'rejected_at' => $this->rejected_at,
                'cancelled_by' => $this->cancelled_by,
                'cancelled_at' => $this->cancelled_at,
                'expired_at' => $this->expired_at,
                'created_at' => $this->created_at,
                'created_by' => $this->created_by,
                'updated_at' => $this->updated_at,
                'updated_by' => $this->updated_by,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'org' => OrgResource::make(
                        $this->whenLoaded('org')
                    ),
                    'asset' => AssetResource::make(
                        $this->whenLoaded('asset')
                    ),
                    'session' => SessionResource::make(
                        $this->whenLoaded('session')
                    ),
                    'asset_account' => AssetAccountResource::make(
                        $this->whenLoaded('assetAccount')
                    ),
                    'requester' => UserResource::make(
                        $this->whenLoaded('requester')
                    ),
                    'approver' => UserResource::make(
                        $this->whenLoaded('approver')
                    ),
                    'rejecter' => UserResource::make(
                        $this->whenLoaded('rejecter')
                    ),
                    'cancelled_by' => UserResource::make(
                        $this->whenLoaded('cancelledBy')
                    ),
                    'created_by' => UserResource::make(
                        $this->whenLoaded('createdBy')
                    ),
                    'updated_by' => UserResource::make(
                        $this->whenLoaded('updatedBy')
                    ),
                ],
            ]),
        ];
    }
}
