<?php

namespace App\Services\Jit;

use App\Exceptions\AuditLogException;
use App\Models\AssetAccount;
use App\Models\Session;
use App\Services\Jit\Database\DatabaseDriverFactory;
use Illuminate\Support\Facades\Log;

class AuditLogManager
{
    public function __construct(
        private DatabaseDriverFactory $driverFactory,
        private CredentialManager $credentialManager,
        private array $config = []
    ) {}
    /**
     * Retrieve query logs for a JIT account during a session
     *
     * @return array<array{timestamp: string, query_text: string, ...}>
     *
     * @throws AuditLogException When database driver creation or query retrieval fails
     */
    public function retrieveQueryLogs(AssetAccount $account, Session $session): array
    {
        try {
            // Get admin credentials for the asset
            $adminCredentials = $this->credentialManager->getAdminCredentials($account->asset);

            // Create database driver
            $driver = $this->driverFactory->create($account->asset, $adminCredentials, $this->config);

            $queryLogs = $driver->retrieveUserQueryLogs(
                $account->username,
                $session->start_datetime ?? $session->created_at,
                $session->end_datetime ?? now()
            );

            return $queryLogs;

        } catch (\Exception $e) {
            Log::error('Failed to retrieve query logs', [
                'session_id' => $session->id,
                'account_id' => $account->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new AuditLogException(
                'Failed to retrieve query logs: '.$e->getMessage(),
                0,
                $e
            );
        }
    }

}
