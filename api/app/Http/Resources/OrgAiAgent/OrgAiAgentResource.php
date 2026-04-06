<?php

namespace App\Http\Resources\OrgAiAgent;

use App\Http\Resources\Resource;
use Illuminate\Http\Request;

class OrgAiAgentResource extends Resource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'attributes' => [
                'id' => $this->id,
                'org_id' => $this->org_id,
                'role' => $this->role instanceof \BackedEnum ? $this->role->value : $this->role,
                'failover' => $this->failover,
                'temperature' => $this->temperature,
                'max_output_tokens' => $this->max_output_tokens,
                'request_timeout_seconds' => $this->request_timeout_seconds,
                'provider_metadata' => $this->provider_metadata,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
        ];
    }
}
