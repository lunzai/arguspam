<?php

namespace Database\Seeders;

use App\Enums\SettingDataType;
use App\Facades\Settings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaultSettings = [
            [
                'key' => 'password.min_length',
                'value' => 8,
                'data_type' => SettingDataType::INTEGER,
                'group' => 'password',
                'label' => 'Minimum Password Length',
                'description' => 'Minimum number of characters required for passwords',
            ],
            [
                'key' => 'password.require_uppercase',
                'value' => true,
                'data_type' => SettingDataType::BOOLEAN,
                'group' => 'password',
                'label' => 'Require Uppercase',
                'description' => 'Whether passwords must contain at least one uppercase letter',
            ],
            [
                'key' => 'notifications.email_enabled',
                'value' => true,
                'data_type' => SettingDataType::BOOLEAN,
                'group' => 'notifications',
                'label' => 'Email Notifications',
                'description' => 'Enable or disable email notifications',
            ],
            [
                'key' => 'theme.primary_color',
                'value' => '#4A7DFF',
                'data_type' => SettingDataType::STRING,
                'group' => 'theme',
                'label' => 'Primary Color',
                'description' => 'Primary theme color for the application',
            ],
        ];

        foreach ($defaultSettings as $setting) {
            try {
                Settings::create($setting);
                $this->command->info("Created setting: {$setting['key']}");
            } catch (\Exception $e) {
                $this->command->warn("Failed to create setting {$setting['key']}: {$e->getMessage()}");
            }
        }
    }
}
