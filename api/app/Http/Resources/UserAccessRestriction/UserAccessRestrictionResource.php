<?php

namespace App\Http\Resources\UserAccessRestriction;

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
            'id' => $this->id,
            'userId' => $this->user_id,
            'type' => $this->type,
            'value' => $this->value,
            'status' => $this->status,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
