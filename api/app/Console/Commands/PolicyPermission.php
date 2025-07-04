<?php

namespace App\Console\Commands;

use App\Services\PolicyPermissionService;
use Illuminate\Console\Command;

class PolicyPermission extends Command
{
    protected $signature = 'permission:sync 
                          {--remove-others : Remove permissions not found in policies}
                          {--dry-run : Show changes without executing}';

    protected $description = 'Sync permissions from policies to database';

    private PolicyPermissionService $service;

    public function __construct(PolicyPermissionService $service)
    {
        parent::__construct();
        $this->service = $service;
    }

    public function handle(): int
    {
        $removeOthers = $this->option('remove-others');
        $dryRun = $this->option('dry-run');
        $changes = $this->service->getChanges($removeOthers);

        $this->showChanges($changes);

        if ($dryRun) {
            $this->info('Dry run complete. No changes were made.');
            return self::SUCCESS;
        }

        if ($removeOthers && $changes['to_remove']->isNotEmpty()) {
            if (!$this->confirm('This will remove existing permissions. Continue?')) {
                return self::FAILURE;
            }
        }

        $this->service->syncPermissions($removeOthers);
        $this->info('Permissions synced successfully!');
        return self::SUCCESS;
    }

    private function showChanges(array $changes): void
    {
        if (
            $changes['to_add']->isEmpty() &&
            $changes['to_remove']->isEmpty() &&
            $changes['unchanged']->isEmpty()
        ) {
            $this->info('No permissions found in policies.');
            return;
        }

        $statusColors = [
            'add' => '<fg=green>New</>',
            'remove' => '<fg=red>To Remove</>',
            'unchanged' => '<fg=gray>Unchanged</>',
        ];

        $rows = collect($changes)
            ->flatMap(function ($items, $type) use ($statusColors) {
                $status = str_replace(['to_', '_'], '', $type);

                return $items->map(function ($item) use ($status, $statusColors) {
                    return [
                        $item['name'],
                        $item['description'],
                        $statusColors[$status],
                    ];
                });
            })
            ->sortBy(0)
            ->values();

        $this->table(
            ['Name', 'Description', 'Status'],
            $rows
        );
    }
}
