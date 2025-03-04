<?php

namespace App\Http\Resources\UserAccessRestriction;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\UserResource;

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
                'userId' => $this->user_id,
                'type' => $this->type,
                'value' => $this->value,
                'status' => $this->status,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
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
