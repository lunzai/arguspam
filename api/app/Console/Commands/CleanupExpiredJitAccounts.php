<?php

namespace App\Console\Commands;

use App\Services\Jit\Secrets\SecretsManager;
use Illuminate\Console\Command;

class CleanupExpiredJitAccounts extends Command
{
    protected $signature = 'pam:cleanup-expired 
                           {--dry-run : Show what would be cleaned without actually cleaning}';

    protected $description = 'Clean up expired JIT database accounts';

    public function handle(SecretsManager $secretsManager): int
    {
        $this->info('Starting cleanup of expired JIT accounts...');

        if ($this->option('dry-run')) {
            $this->warn('Running in dry-run mode. No accounts will be terminated.');

            $expiredCount = \App\Models\AssetAccount::where('type', 'jit')
                ->where('expires_at', '<', now())
                ->where('is_active', true)
                ->count();

            $this->info("Found {$expiredCount} expired JIT accounts.");
            return self::SUCCESS;
        }

        try {
            $cleaned = $secretsManager->cleanupExpiredAccounts();
            $this->info("Successfully cleaned up {$cleaned} expired JIT accounts.");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to cleanup expired accounts: '.$e->getMessage());
            return self::FAILURE;
        }
    }
}
