<?php

namespace App\Console\Commands;

use App\Facades\Settings;
use App\Models\Setting;
use Illuminate\Console\Command;

class SettingsGetCommand extends Command
{
    protected $signature = 'settings:get {key? : The setting key}';
    protected $description = 'Get a setting value';

    public function handle()
    {
        $key = $this->argument('key');

        if (!$key) {
            $allKeys = Setting::pluck('key')->toArray();
            $key = $this->anticipate('Enter setting key:', $allKeys);
        }

        if (!Settings::has($key)) {
            $this->error("Setting '{$key}' not found.");
            return 1;
        }

        $value = Settings::get($key);
        $this->info("Value for '{$key}':");

        if (is_array($value) || is_object($value)) {
            $this->line(json_encode($value, JSON_PRETTY_PRINT));
        } else {
            $this->line((string) $value);
        }

        return 0;
    }
}
