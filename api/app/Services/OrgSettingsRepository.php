<?php

namespace App\Services;

use App\Models\OrgSetting;

class OrgSettingsRepository
{
    public function get(int $orgId, string $key, mixed $default = null): mixed
    {
        $row = OrgSetting::query()
            ->where('org_id', $orgId)
            ->where('key', $key)
            ->first();

        return $row?->value ?? $default;
    }

    /**
     * @param  array<string, mixed>|scalar|null  $value
     */
    public function set(int $orgId, string $key, mixed $value): OrgSetting
    {
        return OrgSetting::query()->updateOrCreate(
            ['org_id' => $orgId, 'key' => $key],
            ['value' => $value]
        );
    }
}
