<?php

namespace App\Http\Resources\SessionAudit;

use App\Http\Resources\Asset\AssetResource;
use App\Http\Resources\Org\OrgResource;
use App\Http\Resources\Request\RequestResource;
use App\Http\Resources\Resource;
use App\Http\Resources\Session\SessionResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;

class SessionAuditResource extends Resource
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
                'sessionId' => $this->session_id,
                'requestId' => $this->request_id,
                'assetId' => $this->asset_id,
                'userId' => $this->user_id,
                'queryText' => $this->query_text,
                'queryTimestamp' => $this->query_timestamp,
                'createdAt' => $this->created_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'org' => OrgResource::make(
                        $this->whenLoaded('org')
                    ),
                    'session' => SessionResource::make(
                        $this->whenLoaded('session')
                    ),
                    'request' => RequestResource::make(
                        $this->whenLoaded('request')
                    ),
                    'asset' => AssetResource::make(
                        $this->whenLoaded('asset')
                    ),
                    'user' => UserResource::make(
                        $this->whenLoaded('user')
                    ),
                ],
            ]),
        ];
    }
}
