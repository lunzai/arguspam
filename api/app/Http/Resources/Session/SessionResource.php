<?php

namespace App\Http\Resources\Session;

use App\Http\Resources\Asset\AssetResource;
use App\Http\Resources\Org\OrgResource;
use App\Http\Resources\Request\RequestResource;
use App\Http\Resources\Resource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;

class SessionResource extends Resource
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
                'requestId' => $this->request_id,
                'assetId' => $this->asset_id,
                'requesterId' => $this->requester_id,
                'startDatetime' => $this->start_datetime,
                'endDatetime' => $this->end_datetime,
                'scheduledEndDatetime' => $this->scheduled_end_datetime,
                'requestedDuration' => $this->requested_duration,
                'actualDuration' => $this->actual_duration,
                'isJit' => $this->is_jit,
                'accountName' => $this->account_name,
                'jitVaultPath' => $this->jit_vault_path,
                'sessionNote' => $this->session_note,
                'isExpired' => $this->is_expired,
                'isTerminated' => $this->is_terminated,
                'isCheckin' => $this->is_checkin,
                'status' => $this->status,
                'checkinBy' => $this->checkin_by,
                'checkinAt' => $this->checkin_at,
                'terminatedBy' => $this->terminated_by,
                'terminatedAt' => $this->terminated_at,
                'endedAt' => $this->ended_at,
                'endedBy' => $this->ended_by,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'org' => OrgResource::collection(
                        $this->whenLoaded('org')
                    ),
                    'request' => RequestResource::collection(
                        $this->whenLoaded('request')
                    ),
                    'asset' => AssetResource::collection(
                        $this->whenLoaded('asset')
                    ),
                    'requester' => UserResource::collection(
                        $this->whenLoaded('requester')
                    ),
                    'checkinBy' => UserResource::collection(
                        $this->whenLoaded('checkinBy')
                    ),
                    'terminatedBy' => UserResource::collection(
                        $this->whenLoaded('terminatedBy')
                    ),
                    'endedBy' => UserResource::collection(
                        $this->whenLoaded('endedBy')
                    ),
                ],
            ]),
        ];
    }
}
