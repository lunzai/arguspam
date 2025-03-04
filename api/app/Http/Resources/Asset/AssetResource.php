<?php

namespace App\Http\Resources\Asset;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'host' => $this->host,
            'port' => $this->port,
            'dbms' => $this->dbms,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
