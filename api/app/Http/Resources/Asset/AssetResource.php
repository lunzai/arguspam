<?php

namespace App\Http\Resources\Asset;

use App\Http\Resources\Org\OrgResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use App\Http\Resources\Resource;

class AssetResource extends Resource
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
                'orgId' => $this->org_id,
                'name' => $this->name,
                'description' => $this->description,
                'status' => $this->status,
                'host' => $this->host,
                'port' => $this->port,
                'dbms' => $this->dbms,
                'createdAt' => $this->created_at,
                'updatedAt' => $this->updated_at,
            ],
            $this->mergeWhen($this->hasRelation(), [
                'relationships' => [
                    'org' => OrgResource::collection(
                        $this->whenLoaded('org')
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
