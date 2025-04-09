<?php

namespace App\Console\Commands;

use App\Enums\SettingDataType;
use App\Facades\Settings;
use Illuminate\Console\Command;

class SettingsCreateCommand extends Command
{
    protected $signature = 'settings:create 
        {key? : The setting key} 
        {--t|type= : Data type (string, integer, float, boolean, json, array)} 
        {--g|group= : Setting group} 
        {--l|label= : Display label} 
        {--d|description= : Setting description}';

    protected $description = 'Create a new setting';

    public function handle()
    {
        $key = $this->argument('key');
        $dataType = $this->option('type');
        $group = $this->option('group');
        $label = $this->option('label');
        $description = $this->option('description');

        if (!$key) {
            $key = $this->ask('Enter setting key (e.g., app.name):');
        }

        if (Settings::has($key)) {
            $this->error("Setting '{$key}' already exists.");
            return 1;
        }

        if (!$dataType) {
            $dataType = $this->choice(
                'Select data type:',
                ['string', 'integer', 'float', 'boolean', 'json', 'array'],
                0
            );
        }

        try {
            $dataTypeEnum = SettingDataType::from($dataType);
        } catch (\ValueError $e) {
            $this->error("Invalid data type '{$dataType}'.");
            return 1;
        }

        if (!$group) {
            $existingGroups = Settings::groups();
            if (!empty($existingGroups)) {
                $group = $this->choice(
                    'Select group or enter a new one:',
                    array_merge($existingGroups, ['[new group]']),
                    count($existingGroups)
                );

                if ($group === '[new group]') {
                    $group = $this->ask('Enter new group name:');
                }
            } else {
                $group = $this->ask('Enter group name:');
            }
        }

        if (!$label) {
            $label = $this->ask('Enter display label:', $key);
        }

        if (!$description) {
            $description = $this->ask('Enter description (optional):');
        }

        $value = $this->askForTypedValue($dataTypeEnum);

        try {
            Settings::create([
                'key' => $key,
                'value' => $value,
                'data_type' => $dataTypeEnum,
                'group' => $group,
                'label' => $label,
                'description' => $description,
            ]);

            $this->info("Setting '{$key}' created successfully.");
        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
            return 1;
        }

        return 0;
    }

    protected function askForTypedValue(SettingDataType $type): mixed
    {
        $value = match ($type) {
            SettingDataType::BOOLEAN => $this->confirm('Enter value (yes/no):', false),
            SettingDataType::INTEGER => (int) $this->ask('Enter integer value:', '0'),
            SettingDataType::FLOAT => (float) $this->ask('Enter float value:', '0.0'),
            SettingDataType::JSON => $this->askJson(),
            SettingDataType::ARRAY => $this->askArray(),
            default => $this->ask('Enter string value:', ''),
        };

        return $value;
    }

    protected function askJson(): array
    {
        $this->info('Enter JSON value (leave empty and press enter to finish):');
        $lines = [];

        while (true) {
            $line = $this->ask('> ');
            if (empty($line)) {
                break;
            }
            $lines[] = $line;
        }

        $jsonString = implode(' ', $lines);

        try {
            return json_decode($jsonString, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->error('Invalid JSON. Using empty object instead.');
            return [];
        }
    }

    protected function askArray(): array
    {
        $this->info('Enter array values (one per line, leave empty and press enter to finish):');
        $values = [];

        while (true) {
            $value = $this->ask('> ');
            if (empty($value)) {
                break;
            }
            $values[] = $value;
        }

        return $values;
    }
}
