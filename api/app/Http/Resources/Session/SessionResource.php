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
                'org_id' => $this->org_id,
                'request_id' => $this->request_id,
                'asset_id' => $this->asset_id,
                'requester_id' => $this->requester_id,
                'start_datetime' => $this->start_datetime,
                'end_datetime' => $this->end_datetime,
                'scheduled_end_datetime' => $this->scheduled_end_datetime,
                'requested_duration' => $this->requested_duration,
                'actual_duration' => $this->actual_duration,
                'is_jit' => $this->is_jit,
                'account_name' => $this->account_name,
                'jit_vault_path' => $this->jit_vault_path,
                'session_note' => $this->session_note,
                'is_expired' => $this->is_expired,
                'is_terminated' => $this->is_terminated,
                'is_checkin' => $this->is_checkin,
                'status' => $this->status,
                'checkin_by' => $this->checkin_by,
                'checkin_at' => $this->checkin_at,
                'terminated_by' => $this->terminated_by,
                'terminated_at' => $this->terminated_at,
                'ended_at' => $this->ended_at,
                'ended_by' => $this->ended_by,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'org' => OrgResource::make(
                        $this->whenLoaded('org')
                    ),
                    'request' => RequestResource::make(
                        $this->whenLoaded('request')
                    ),
                    'asset' => AssetResource::make(
                        $this->whenLoaded('asset')
                    ),
                    'requester' => UserResource::make(
                        $this->whenLoaded('requester')
                    ),
                    'checkinBy' => UserResource::make(
                        $this->whenLoaded('checkinBy')
                    ),
                    'terminatedBy' => UserResource::make(
                        $this->whenLoaded('terminatedBy')
                    ),
                    'endedBy' => UserResource::make(
                        $this->whenLoaded('endedBy')
                    ),
                ],
            ]),
        ];
    }
}
