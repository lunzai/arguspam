<?php

namespace App\Http\Resources\Org;

use App\Http\Resources\Resource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrgResource extends Resource
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
                'name' => $this->name,
                'description' => $this->description,
                'status' => $this->status,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
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
