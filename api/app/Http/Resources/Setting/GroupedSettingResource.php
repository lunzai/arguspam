<?php

namespace App\Http\Resources\Setting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupedSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        // Transform the settings to a key-value structure
        $formattedSettings = [];

        foreach ($this->resource as $key => $value) {
            $formattedSettings[$key] = $value;
        }

        return $formattedSettings;
    }
}
