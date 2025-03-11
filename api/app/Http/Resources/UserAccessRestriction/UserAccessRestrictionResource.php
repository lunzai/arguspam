<?php

namespace App\Http\Resources\UserAccessRestriction;

use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAccessRestrictionResource extends JsonResource
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
                'user_id' => $this->user_id,
                'type' => $this->type,
                'value' => $this->value,
                'status' => $this->status,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            $this->mergeWhen(count($this->resource->getRelations()) > 0, [
                'relationships' => [
                    'user' => UserResource::collection(
                        $this->whenLoaded('user')
                    ),
                ],
            ]),
        ];
    }
}
