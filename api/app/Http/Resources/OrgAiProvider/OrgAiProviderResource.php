<?php

namespace App\Http\Resources\OrgAiProvider;

use App\Http\Resources\Resource;
use Illuminate\Http\Request;

class OrgAiProviderResource extends Resource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'attributes' => [
                'id' => $this->id,
                'org_id' => $this->org_id,
                'sort_order' => $this->sort_order,
                'driver' => $this->driver,
                'label' => $this->label,
                'options' => $this->options,
                'has_api_key' => $this->hasApiKey(),
                'is_enabled' => $this->is_enabled,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
        ];
    }

    private function hasApiKey(): bool
    {
        $key = $this->resource->api_key;

        return is_string($key) && $key !== '';
    }
}
