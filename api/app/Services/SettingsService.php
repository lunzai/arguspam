<?php

namespace App\Services;

use App\Enums\CacheKey;
use App\Enums\SettingDataType;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SettingsService
{
    protected int $cacheTtl = 86400; // 24 hours

    /**
     * Get a setting by key
     */
    public function get(string $key, $default = null)
    {
        return Cache::remember(
            CacheKey::SETTING_VALUE->value . ':' . $key,
            config('cache.default_ttl'),
            function () use ($key, $default) {
                $setting = Setting::where('key', $key)->first();
                return $setting ? $setting->typed_value : $default;
            }
        );
    }

    /**
     * Check if a setting exists
     */
    public function has(string $key): bool
    {
        return Cache::remember(
            CacheKey::SETTING_VALUE->value . ':' . $key,
            config('cache.default_ttl'),
            function () use ($key) {
                return Setting::where('key', $key)->exists();
            }
        );
    }

    /**
     * Set a setting value
     */
    public function set(string|array $key, $value = null): bool
    {
        if (is_array($key)) {
            return $this->setMany($key);
        }

        DB::transaction(function () use ($key, $value) {
            $setting = Setting::where('key', $key)->first();

            if ($setting) {
                $setting->typed_value = $value;
                $setting->save();
            } else {
                throw new \Exception("Setting with key '{$key}' not found. Use create() to add new settings.");
            }
        });

        // Invalidate caches
        $this->invalidateCache($key);

        return true;
    }

    /**
     * Update multiple settings at once
     */
    protected function setMany(array $settings): bool
    {
        DB::transaction(function () use ($settings) {
            foreach ($settings as $key => $value) {
                $this->set($key, $value);
            }
        });

        return true;
    }

    /**
     * Create a new setting
     */
    public function create(array $data): Setting
    {
        // Validate data type
        $dataType = $data['data_type'] ?? SettingDataType::STRING;
        if (is_string($dataType)) {
            $dataType = SettingDataType::from($dataType);
        }

        if (!$dataType->validate($data['value'])) {
            throw new \InvalidArgumentException("Invalid value for type {$dataType->value}");
        }

        $setting = DB::transaction(function () use ($data, $dataType) {
            $setting = new Setting;
            $setting->key = $data['key'];
            $setting->data_type = $dataType;
            $setting->typed_value = $data['value'];
            $setting->group = $data['group'] ?? null;
            $setting->label = $data['label'] ?? $data['key'];
            $setting->description = $data['description'] ?? null;
            $setting->save();

            return $setting;
        });

        // Invalidate group and all caches
        Cache::forget(CacheKey::SETTING_ALL->value);
        if ($setting->group) {
            Cache::forget(CacheKey::SETTING_GROUP->value . ':' . $setting->group);
        }
        Cache::forget(CacheKey::SETTING_GROUP_ALL->value);

        return $setting;
    }

    /**
     * Get all settings, grouped by group
     */
    public function all()
    {
        return Cache::remember(
            CacheKey::SETTING_ALL->value,
            config('cache.default_ttl'),
            function () {
                $settings = Setting::all();
                $result = [];
                foreach ($settings as $setting) {
                    $group = $setting->group ?? 'general';
                    $result[$group][$setting->key] = $setting->typed_value;
                }
                return $result;
            }
        );
    }

    /**
     * Get settings by group
     */
    public function group(string $group)
    {
        return Cache::remember(
            CacheKey::SETTING_GROUP->value . ':' . $group,
            config('cache.default_ttl'),
            function () use ($group) {
                $settings = Setting::where('group', $group)->get();
                $result = [];

                foreach ($settings as $setting) {
                    $result[$setting->key] = $setting;
                }

                return $result;
            }
        );
    }

    /**
     * Rename a group
     */
    public function renameGroup(string $oldName, string $newName): bool
    {
        DB::transaction(function () use ($oldName, $newName) {
            Setting::where('group', $oldName)->update(['group' => $newName]);
        });

        // Invalidate caches
        Cache::forget(CacheKey::SETTING_ALL->value);
        Cache::forget(CacheKey::SETTING_GROUP->value . ':' . $oldName);
        Cache::forget(CacheKey::SETTING_GROUP->value . ':' . $newName);
        Cache::forget(CacheKey::SETTING_GROUP_ALL->value);

        return true;
    }

    /**
     * Get all groups
     */
    public function groups(): array
    {
        return Cache::remember(
            CacheKey::SETTING_GROUP_ALL->value,
            config('cache.default_ttl'),
            function () {
                return Setting::select('group')
                    ->distinct()
                    ->whereNotNull('group')
                    ->pluck('group')
                    ->toArray();
            }
        );
    }

    /**
     * Generate a slug from a key
     */
    protected function generateSlug(string $key): string
    {
        return str::slug($key);
    }

    /**
     * Invalidate caches for a setting
     */
    protected function invalidateCache(string $key): void
    {
        $setting = Setting::where('key', $key)->first();

        if ($setting) {
            Cache::forget(CacheKey::SETTING_KEY->value . ':' . $key);
            Cache::forget(CacheKey::SETTING_VALUE->value . ':' . $key);
            Cache::forget(CacheKey::SETTING_ALL->value);

            if ($setting->group) {
                Cache::forget(CacheKey::SETTING_GROUP->value . ':' . $setting->group);
            }

            Cache::forget(CacheKey::SETTING_GROUP_ALL->value);
        }
    }
}
