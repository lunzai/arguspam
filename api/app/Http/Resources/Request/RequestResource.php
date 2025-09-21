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
                'reason' => $this->reason,
                'intended_query' => $this->intended_query,
                'scope' => $this->scope,
                'is_access_sensitive_data' => $this->is_access_sensitive_data,
                'sensitive_data_note' => $this->sensitive_data_note,
                'approver_note' => $this->approver_note,
                'approver_risk_rating' => $this->approver_risk_rating,
                'ai_note' => $this->ai_note,
                'ai_risk_rating' => $this->ai_risk_rating,
                'status' => $this->status,
                'approved_by' => $this->approved_by,
                'approved_at' => $this->approved_at,
                'rejected_by' => $this->rejected_by,
                'rejected_at' => $this->rejected_at,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
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
                ],
            ]),
        ];
    }
}
