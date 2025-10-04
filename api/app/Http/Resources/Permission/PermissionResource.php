<?php

namespace App\Http\Resources\Permission;

use App\Http\Resources\Resource;
use App\Http\Resources\Role\RoleResource;
use Illuminate\Http\Request;

class PermissionResource extends Resource
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
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'roles' => RoleResource::collection(
                        $this->whenLoaded('roles')
                    ),
                ],
            ]),
        ];
    }
}
