<?php

namespace App\Http\Resources\AssetAccount;

use App\Http\Resources\Asset\AssetResource;
use App\Http\Resources\Resource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;

class AssetAccountResource extends Resource
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
                'assetId' => $this->asset_id,
                'name' => $this->name,
                'vaultPath' => $this->vault_path,
                'isDefault' => $this->is_default,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'asset' => AssetResource::collection(
                        $this->whenLoaded('asset')
                    ),
                    'createdBy' => UserResource::collection(
                        $this->whenLoaded('createdBy')
                    ),
                    'updatedBy' => UserResource::collection(
                        $this->whenLoaded('updatedBy')
                    ),
                ],
            ]),
        ];
    }
}
