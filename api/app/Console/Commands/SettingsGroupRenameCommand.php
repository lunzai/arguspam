<?php

namespace App\Console\Commands;

use App\Facades\Settings;
use Illuminate\Console\Command;

class SettingsGroupRenameCommand extends Command
{
    protected $signature = 'settings:group:rename {old? : Old group name} {new? : New group name}';
    protected $description = 'Rename a settings group';

    public function handle()
    {
        $oldName = $this->argument('old');
        $newName = $this->argument('new');

        $groups = Settings::groups();

        if (!$oldName) {
            $oldName = $this->choice('Select group to rename:', $groups);
        } elseif (!in_array($oldName, $groups)) {
            $this->error("Group '{$oldName}' not found.");
            return 1;
        }

        if (!$newName) {
            $newName = $this->ask('Enter new group name:');
        }

        if ($this->confirm("Rename group '{$oldName}' to '{$newName}'?")) {
            Settings::renameGroup($oldName, $newName);
            $this->info('Group renamed successfully.');
        } else {
            $this->info('Operation cancelled.');
        }

        return 0;
    }
}
