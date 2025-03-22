<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class PermissionFlush extends Command
{
    protected $signature = 'permission:flush 
                          {--f|force : Force the operation without confirmation}';
    protected $description = 'Delete all permissions from the database';

    public function handle(): int
    {
        $count = Permission::count();

        if ($count === 0) {
            info('No permissions found in database.');
            return self::SUCCESS;
        }
        warning("Found {$count} permissions in database.");

        if (
            !$this->option('force') &&
            !confirm(label: 'Are you sure you want to delete all permissions?', default: false)
        ) {
            info('Operation cancelled.');
            return self::SUCCESS;
        }
        info('Deleting permissions...');
        // Use delete() instead of truncate() to handle cascading deletes
        Permission::query()->delete();
        info('All permissions have been deleted.');
        return self::SUCCESS;
    }
}
