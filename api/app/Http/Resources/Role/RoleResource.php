<?php

namespace App\Http\Resources\Role;

use App\Http\Resources\Permission\PermissionResource;
use App\Http\Resources\Resource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;

class RoleResource extends Resource
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
                'name' => e($this->name),
                'description' => e($this->description),
                'is_default' => $this->is_default,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'users' => UserResource::collection(
                        $this->whenLoaded('users')
                    ),
                    'permissions' => PermissionResource::collection(
                        $this->whenLoaded('permissions')
                    ),
                ],
            ]),
        ];
    }
}
