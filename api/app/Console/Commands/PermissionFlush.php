<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;

class PermissionFlush extends Command
{
    protected $signature = 'permission:flush 
                          {--f|force : Force the operation without confirmation}';
    protected $description = 'Delete all permissions from the database';

    public function handle(): int
    {
        $count = Permission::count();

        if ($count === 0) {
            $this->info('No permissions found in database.');
            return self::SUCCESS;
        }
        $this->warn("Found {$count} permissions in database.");

        if (!$this->option('force') && !$this->confirm('Are you sure you want to delete all permissions?')) {
            $this->info('Operation cancelled.');
            return self::SUCCESS;
        }
        $this->info('Deleting permissions...');
        // Use delete() instead of truncate() to handle cascading deletes
        Permission::query()->delete();
        $this->info('All permissions have been deleted.');
        return self::SUCCESS;
    }
}
