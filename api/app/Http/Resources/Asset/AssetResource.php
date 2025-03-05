<?php

namespace App\Http\Resources\Asset;

use App\Http\Resources\Org\OrgResource;
use App\Http\Resources\User\UserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
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
            $this->mergeWhen(count($this->resource->getRelations()) > 0, [
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
