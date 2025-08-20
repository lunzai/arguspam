<?php

namespace App\Http\Resources\AssetAccount;

use App\Http\Resources\Asset\AssetResource;
use App\Http\Resources\Request\RequestResource;
use App\Http\Resources\Resource;
use App\Http\Resources\Session\SessionResource;
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
                'asset_id' => $this->asset_id,
                'username' => $this->username,
                'type' => $this->type,
                'expires_at' => $this->expires_at,
                'is_active' => $this->is_active,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'asset' => AssetResource::collection(
                        $this->whenLoaded('asset')
                    ),
                    'requests' => RequestResource::collection(
                        $this->whenLoaded('requests')
                    ),
                    'sessions' => SessionResource::collection(
                        $this->whenLoaded('sessions')
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
