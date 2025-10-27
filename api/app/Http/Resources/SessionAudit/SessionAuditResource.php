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
                'org_id' => $this->org_id,
                'session_id' => $this->session_id,
                'asset_id' => $this->asset_id,
                'user_id' => $this->user_id,
                'username' => $this->username,
                'query' => $this->query,
                'command_type' => $this->command_type,
                'count' => $this->count,
                'first_timestamp' => $this->first_timestamp,
                'last_timestamp' => $this->last_timestamp,
                'created_at' => $this->created_at,
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
