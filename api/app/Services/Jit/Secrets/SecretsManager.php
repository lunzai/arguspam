<?php

namespace App\Services\Jit\Secrets;

use App\Enums\AssetAccountType;
use App\Events\SessionJitCreated;
use App\Exceptions\AuditLogException;
use App\Exceptions\CredentialNotFoundException;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Session;
use App\Models\SessionAudit;
use App\Services\Jit\Databases\Contracts\DatabaseDriverInterface;
use App\Services\Jit\Databases\DatabaseDriverFactory;
use Exception;
use Illuminate\Support\Facades\Log;

class SecretsManager
{
    public function __construct(
        private DatabaseDriverFactory $driverFactory,
        private ?array $config = null
    ) {
        $this->config = $config ?? config('pam.database', []);
    }

    public function getAllDatabases(Asset $asset): array
    {
        $databaseDriver = $this->getDatabaseDriver($asset);
        return $databaseDriver->getAllDatabases();
    }

    public function getAdminCredentials(Asset $asset): array
    {
        $adminAccount = $asset->adminAccount;
        if (!$adminAccount) {
            throw new CredentialNotFoundException("No active admin account found for asset: {$asset->name}");
        }
        return [
            'host' => $asset->host,
            'port' => $asset->port,
            'database' => $asset->database,
            'username' => $adminAccount->username,
            'password' => $adminAccount->password,
            'databases' => $adminAccount->databases,
        ];
    }

    /**
     * Get appropriate database driver for asset
     */
    private function getDatabaseDriver(Asset $asset): DatabaseDriverInterface
    {
        $adminCredentials = $this->getAdminCredentials($asset);
        $driver = $this->driverFactory->create(
            $asset,
            $adminCredentials,
            $this->config
        );
        if (!$driver->testAdminConnection($adminCredentials)) {
            throw new Exception('Failed to connect to database with admin credentials');
        }
        return $driver;
    }
    /**
     * Create JIT account for session
     *
     * @return AssetAccount The created JIT account
     *
     * @throws Exception When account creation fails
     */
    public function createAccount(Session $session): AssetAccount
    {
        try {
            $databaseDriver = $this->getDatabaseDriver($session->asset);
            $jitUsername = $databaseDriver->generateUsername();
            $jitPassword = $databaseDriver->generatePassword();
            $success = $databaseDriver->createUser(
                $jitUsername,
                $jitPassword,
                $session->request->databases,
                $session->request->scope,
                $session->scheduled_end_datetime
            );
            if (!$success) {
                throw new Exception("Failed to create JIT user in database for session: {$session->id}");
            }
            $databaseDriver->connection->beginTransaction();
            try {
                $jitAccount = AssetAccount::create([
                    'asset_id' => $session->asset->id,
                    'username' => $jitUsername,
                    'password' => $jitPassword,
                    'databases' => $session->request->databases,
                    'type' => AssetAccountType::JIT,
                    'expires_at' => $session->scheduled_end_datetime,
                    'is_active' => true,
                ]);
                $updatedSession = $session->update([
                    'asset_account_id' => $jitAccount->id,
                    'account_name' => $jitUsername,
                ]);
                SessionJitCreated::dispatchIf($updatedSession && $jitAccount, $updatedSession);
                $databaseDriver->connection->commit();
                return $jitAccount;
            } catch (Exception $e) {
                $databaseDriver->connection->rollBack();
                $databaseDriver->terminateUser($jitUsername, $session->request->databases);
                throw $e;
            }
        } catch (Exception $e) {
            Log::error('Failed to create JIT account for session', ['session_id' => $session->id, 'error' => $e->getMessage()]);
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Terminate JIT account and collect audit logs
     */
    public function terminateAccount(Session $session): bool
    {
        try {
            $jitAccount = $session->assetAccount;
            if (!$jitAccount || $jitAccount->type !== AssetAccountType::JIT) {
                Log::warning('No JIT account found for session', ['session_id' => $session->id]);
                return false;
            }
            $databaseDriver = $this->getDatabaseDriver($session->asset);
            try {
                $queryLogs = $databaseDriver->retrieveUserQueryLogs($jitAccount->username, $session->start_datetime ?? $session->created_at, $session->end_datetime ?? now());
                if (!empty($queryLogs)) {
                    SessionAudit::storeForSession($session, $queryLogs);
                    Log::info('Retrieved and stored audit logs', [
                        'session_id' => $session->id,
                        'log_count' => count($queryLogs),
                    ]);
                }
            } catch (AuditLogException $e) {
                Log::warning('Failed to retrieve audit logs during termination', [
                    'session_id' => $session->id,
                    'error' => $e->getMessage(),
                ]);
                // Continue with termination even if audit log retrieval fails
            }
            return $databaseDriver->terminateUser($jitAccount->username, $session->request->databases);
        } catch (Exception $e) {
            Log::error('Failed to terminate JIT account', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Clean up expired JIT accounts
     */
    public function cleanupExpiredAccounts(): int
    {
        $expiredAccounts = AssetAccount::find()
            ->jit()
            ->expired()
            ->get();
        foreach ($expiredAccounts as $account) {
            $this->terminateAccount($account->session);
        }
        return count($expiredAccounts);
    }
}
