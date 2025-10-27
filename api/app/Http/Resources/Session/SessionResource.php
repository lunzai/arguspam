<?php

namespace App\Http\Resources\Session;

use App\Http\Resources\Asset\AssetResource;
use App\Http\Resources\AssetAccount\AssetAccountResource;
use App\Http\Resources\Org\OrgResource;
use App\Http\Resources\Request\RequestResource;
use App\Http\Resources\Resource;
use App\Http\Resources\SessionAudit\SessionAuditResource;
use App\Http\Resources\SessionFlag\SessionFlagResource;
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
                'asset_account_id' => $this->asset_account_id,
                'requester_id' => $this->requester_id,
                'approver_id' => $this->approver_id,
                'start_datetime' => $this->start_datetime,
                'end_datetime' => $this->end_datetime,
                'scheduled_start_datetime' => $this->scheduled_start_datetime,
                'scheduled_end_datetime' => $this->scheduled_end_datetime,
                'requested_duration' => $this->requested_duration,
                'actual_duration' => $this->actual_duration,
                'is_admin_account' => $this->is_admin_account,
                'account_name' => $this->account_name,
                'session_activity_risk' => $this->session_activity_risk,
                'deviation_risk' => $this->deviation_risk,
                'overall_risk' => $this->overall_risk,
                'ai_note' => e($this->ai_note),
                'ai_reviewed_at' => $this->ai_reviewed_at,
                'session_note' => e($this->session_note),
                'status' => $this->status,
                'account_created_at' => $this->account_created_at,
                'account_revoked_at' => $this->account_revoked_at,
                'human_audit_confidence' => $this->human_audit_confidence,
                'human_audit_required' => $this->human_audit_required,
                'started_by' => $this->started_by,
                'started_at' => $this->started_at,
                'ended_by' => $this->ended_by,
                'ended_at' => $this->ended_at,
                'cancelled_by' => $this->cancelled_by,
                'cancelled_at' => $this->cancelled_at,
                'terminated_by' => $this->terminated_by,
                'terminated_at' => $this->terminated_at,
                'expired_at' => $this->expired_at,
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
                    'assetAccount' => AssetAccountResource::make(
                        $this->whenLoaded('assetAccount')
                    ),
                    'requester' => UserResource::make(
                        $this->whenLoaded('requester')
                    ),
                    'approver' => UserResource::make(
                        $this->whenLoaded('approver')
                    ),
                    'startedBy' => UserResource::make(
                        $this->whenLoaded('startedBy')
                    ),
                    'endedBy' => UserResource::make(
                        $this->whenLoaded('endedBy')
                    ),
                    'cancelledBy' => UserResource::make(
                        $this->whenLoaded('cancelledBy')
                    ),
                    'terminatedBy' => UserResource::make(
                        $this->whenLoaded('terminatedBy')
                    ),
                    'flags' => SessionFlagResource::collection(
                        $this->whenLoaded('flags')
                    ),
                    'audits' => SessionAuditResource::collection(
                        $this->whenLoaded('audits')
                    ),
                    'createdBy' => UserResource::make(
                        $this->whenLoaded('createdBy')
                    ),
                ],
            ]),
        ];
    }
}
