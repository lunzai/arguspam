<?php

namespace App\Console\Commands;

use App\Enums\SettingDataType;
use App\Facades\Settings;
use App\Models\Setting;
use Illuminate\Console\Command;

class SettingsSetCommand extends Command
{
    protected $signature = 'settings:set {key? : The setting key} {value? : The new value}';
    protected $description = 'Update a setting value';

    public function handle()
    {
        $key = $this->argument('key');
        $inputValue = $this->argument('value');

        if (!$key) {
            $allKeys = Setting::pluck('key')->toArray();
            $key = $this->anticipate('Enter setting key:', $allKeys);
        }

        $setting = Setting::where('key_slug', str_replace(['.', ' ', '-'], '_', strtolower($key)))->first();

        if (!$setting) {
            $this->error("Setting '{$key}' not found.");
            return 1;
        }

        $currentValue = Settings::get($key);

        if (!$inputValue) {
            $inputValue = $this->ask("Enter new value for '{$key}':",
                is_bool($currentValue) ? ($currentValue ? 'true' : 'false') :
                (is_array($currentValue) ? json_encode($currentValue) : (string) $currentValue));
        }

        // Pre-process value based on data type
        try {
            $parsedValue = $this->parseValueForType($inputValue, $setting->data_type);

            if ($this->confirm("Update '{$key}' from ".$this->formatValue($currentValue).' to '.$this->formatValue($parsedValue).'?')) {
                Settings::set($key, $parsedValue);
                $this->info("Setting '{$key}' updated successfully.");
            } else {
                $this->info('Operation cancelled.');
            }
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function parseValueForType(string $value, SettingDataType $type): mixed
    {
        return match ($type) {
            SettingDataType::BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            SettingDataType::INTEGER => (int) $value,
            SettingDataType::FLOAT => (float) $value,
            SettingDataType::JSON, SettingDataType::ARRAY => json_decode($value, true),
            default => $value,
        };
    }

    protected function formatValue($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return (string) $value;
    }
}
