<?php

namespace App\Console\Commands;

use App\Facades\Settings;
use Illuminate\Console\Command;
use Laravel\Prompts\text;
use Laravel\Prompts\select;
use Laravel\Prompts\confirm;

use function Laravel\Prompts\info;
use function Laravel\Prompts\table;

class SettingsListCommand extends Command
{
    protected $signature = 'settings:list {--group= : Filter by group}';
    protected $description = 'List all settings';

    public function handle()
    {
        $group = $this->option('group');

        if ($group) {
            if (!in_array($group, Settings::groups())) {
                $group = $this->choice(
                    'Group not found. Select a group:',
                    Settings::groups()
                );
            }

            $settings = Settings::group($group);
            info("Settings for group: {$group}");

            $rows = [];
            foreach ($settings as $key => $value) {
                $rows[] = [
                    $key,
                    $this->formatValue($value)
                ];
            }
            table(['Key', 'Slug', 'Value'], $rows);
            return;
        }

        $allSettings = Settings::all();

        $groupCount = count($allSettings);
        $settingCount = 0;
        foreach ($allSettings as $group => $settings) {
            $groupCount++;
            $settingCount += count($settings);
            $this->info("Group: {$group}");

            $rows = [];
            foreach ($settings as $key => $value) {
                $rows[] = [$key, $this->formatValue($value)];
            }

            $this->table(['Key', 'Value'], $rows);
            $this->newLine();
        }


    }

    protected function formatValue($value)
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
