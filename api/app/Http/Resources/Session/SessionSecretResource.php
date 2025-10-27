<?php

namespace App\Http\Resources\Session;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionSecretResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => [
                'host' => $this->asset->host,
                'port' => $this->asset->port,
                'database' => $this->assetAccount->databases,
                'username' => $this->assetAccount->username,
                'password' => $this->assetAccount->password,
            ],
        ];
    }
}
