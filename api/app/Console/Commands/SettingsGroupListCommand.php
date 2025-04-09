<?php

namespace App\Console\Commands;

use App\Facades\Settings;
use Illuminate\Console\Command;

class SettingsGroupListCommand extends Command
{
    protected $signature = 'settings:group:list';
    protected $description = 'List all setting groups';

    public function handle()
    {
        $groups = Settings::groups();

        $this->info('Available groups:');

        $rows = [];
        foreach ($groups as $group) {
            $count = count(Settings::group($group));
            $rows[] = [$group, $count];
        }

        $this->table(['Group', 'Settings Count'], $rows);

        return 0;
    }
}
