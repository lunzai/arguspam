<?php

namespace App\Http\Resources\AssetAccount;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Asset\AssetResource;
use App\Http\Resources\User\UserResource;

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
            'attributes' => [
                'id' => $this->id,
                'assetId' => $this->asset_id,
                'name' => $this->name,
                'vaultPath' => $this->vault_path,
                'isDefault' => $this->is_default,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            $this->mergeWhen(count($this->resource->getRelations()) > 0, [
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
