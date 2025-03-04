<?php

namespace App\Http\Resources\Session;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
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
        ];
    }
}
