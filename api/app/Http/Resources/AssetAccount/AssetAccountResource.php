<?php

namespace App\Http\Resources\AssetAccount;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetAccountResource extends JsonResource
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
            'assetId' => $this->asset_id,
            'name' => $this->name,
            'vaultPath' => $this->vault_path,
            'isDefault' => $this->is_default,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
