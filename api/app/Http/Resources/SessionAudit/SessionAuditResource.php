<?php

namespace App\Http\Resources\SessionAudit;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionAuditResource extends JsonResource
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
            'sessionId' => $this->session_id,
            'requestId' => $this->request_id,
            'assetId' => $this->asset_id,
            'userId' => $this->user_id,
            'queryText' => $this->query_text,
            'queryTimestamp' => $this->query_timestamp,
            'createdAt' => $this->created_at,
        ];
    }
}
