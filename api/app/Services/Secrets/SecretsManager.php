<?php

namespace App\Services\Secrets;

use App\Enums\AssetAccountType;
use App\Enums\RequestScope;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Session;
use App\Models\SessionAudit;
use App\Services\Database\Contracts\DatabaseDriverInterface;
use App\Services\Database\DatabaseDriverFactory;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SecretsManager
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('secrets');
    }

    /**
     * Create JIT account for session
     */
    public function createAccount(Session $session): AssetAccount
    {
        Log::info('Creating JIT account for session', ['session_id' => $session->id]);

        try {
            return DB::transaction(function () use ($session) {
                // Get admin credentials
                $adminCreds = $this->getAdminCredentials($session->asset);

                // Get database driver
                $driver = $this->getDatabaseDriver($session->asset, $adminCreds);

                // Generate JIT credentials
                $jitCreds = $driver->generateSecureCredentials();

                // Determine database access - can be single, multiple, or all databases
                $databases = $this->determineDatabaseAccess($session, $adminCreds);

                // Create user in database
                $success = $driver->createUser(
                    $jitCreds['username'],
                    $jitCreds['password'],
                    $databases,
                    $session->request->scope,
                    $session->scheduled_end_datetime
                );

                if (!$success) {
                    throw new Exception('Failed to create JIT user in database');
                }

                // Store JIT account in database
                $jitAccount = AssetAccount::create([
                    'asset_id' => $session->asset_id,
                    'name' => "JIT account for session {$session->id}",
                    'username' => $jitCreds['username'],
                    'password' => Crypt::encryptString($jitCreds['password']),
                    'type' => AssetAccountType::JIT,
                    'expires_at' => $session->scheduled_end_datetime,
                    'is_active' => true,
                    'created_by' => Auth::id() ?? $session->requester_id,
                    'updated_by' => Auth::id() ?? $session->requester_id,
                ]);

                // Update session with JIT account
                $session->update([
                    'asset_account_id' => $jitAccount->id,
                    'account_name' => $jitCreds['username'],
                ]);

                Log::info('JIT account created successfully', [
                    'session_id' => $session->id,
                    'account_id' => $jitAccount->id,
                    'username' => $jitCreds['username'],
                ]);

                return $jitAccount;
            });

        } catch (Exception $e) {
            Log::error('Failed to create JIT account', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Terminate JIT account and collect audit logs
     */
    public function terminateAccount(Session $session): array
    {
        Log::info('Terminating JIT account for session', ['session_id' => $session->id]);

        $results = [
            'terminated' => false,
            'audit_logs_retrieved' => false,
            'account_deleted' => false,
            'errors' => [],
        ];

        try {
            // Get JIT account if exists
            $jitAccount = $session->assetAccount;

            if (!$jitAccount || $jitAccount->type !== AssetAccountType::JIT) {
                Log::warning('No JIT account found for session', ['session_id' => $session->id]);
                return $results;
            }

            // Get admin credentials
            $adminCreds = $this->getAdminCredentials($session->asset);
            $driver = $this->getDatabaseDriver($session->asset, $adminCreds);

            // Retrieve query logs before termination
            try {
                $queryLogs = $this->retrieveQueryLogs($jitAccount, $session, $driver);
                $results['audit_logs_retrieved'] = true;
                $results['audit_log_count'] = count($queryLogs);

                // Store audit logs
                $this->storeAuditLogs($session, $queryLogs);

            } catch (Exception $e) {
                $results['errors'][] = 'Failed to retrieve query logs: '.$e->getMessage();
                Log::error('Failed to retrieve query logs', [
                    'session_id' => $session->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Terminate user in database
            try {
                $database = $adminCreds['database'] ?? $session->asset->name;
                $terminated = $driver->terminateUser($jitAccount->username, $database);
                $results['terminated'] = $terminated;

            } catch (Exception $e) {
                $results['errors'][] = 'Failed to terminate database user: '.$e->getMessage();
                Log::error('Failed to terminate database user', [
                    'session_id' => $session->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Delete JIT account record
            try {
                $jitAccount->delete();
                $results['account_deleted'] = true;

            } catch (Exception $e) {
                $results['errors'][] = 'Failed to delete account record: '.$e->getMessage();
                Log::error('Failed to delete account record', [
                    'account_id' => $jitAccount->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('JIT account termination completed', [
                'session_id' => $session->id,
                'results' => $results,
            ]);

        } catch (Exception $e) {
            $results['errors'][] = 'General termination error: '.$e->getMessage();
            Log::error('Failed to terminate JIT account', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    /**
     * Determine database access for JIT account creation
     * 
     * @param Session $session The session requesting access
     * @param array $adminCreds Admin credentials
     * @return string|array|null Database name(s) or null for all databases
     */
    private function determineDatabaseAccess(Session $session, array $adminCreds): string|array|null
    {
        // Check if admin credentials specify databases
        if (isset($adminCreds['databases'])) {
            return $adminCreds['databases'];
        }

        // Check if admin credentials specify a single database
        if (isset($adminCreds['database'])) {
            return $adminCreds['database'];
        }

        // Check if session has specific database requirements
        if (isset($session->request->databases)) {
            return $session->request->databases;
        }

        // Check if session has a single database requirement
        if (isset($session->request->database)) {
            return $session->request->database;
        }

        // Check if asset has specific database configuration
        if (isset($session->asset->databases)) {
            return $session->asset->databases;
        }

        if (isset($session->asset->database)) {
            return $session->asset->database;
        }

        // Default: grant access to all databases
        return null;
    }

    /**
     * Get decrypted admin credentials for an asset
     */
    public function getAdminCredentials(Asset $asset): array
    {
        $adminAccount = $asset->accounts()
            ->admin()
            ->active()
            ->first();

        if (!$adminAccount) {
            throw new Exception("No active admin account found for asset: {$asset->name}");
        }

        return [
            'username' => $adminAccount->username,
            'password' => $adminAccount->password,
            // 'database' => $asset->database,
        ];
    }

    /**
     * Generate secure credentials for new accounts
     */
    public function generateCredentials(Asset $asset): array
    {
        $driver = DatabaseDriverFactory::create($asset, [
            'username' => 'temp',
            'password' => 'temp',
        ], $this->config);

        return $driver->generateSecureCredentials();
    }

    /**
     * Get appropriate database driver for asset
     */
    public function getDatabaseDriver(Asset $asset, ?array $credentials = null): DatabaseDriverInterface
    {
        $adminCreds = $credentials ?? $this->getAdminCredentials($asset);
        $fullCredentials = [
            'host' => $asset->host,
            'port' => $asset->port,
            'database' => $adminCreds['database'] ?? 'arguspam',
            'username' => $adminCreds['username'],
            'password' => $adminCreds['password'],
        ];
        
        $driver = DatabaseDriverFactory::create($asset, $fullCredentials, $this->config);
        // Test connection (this will establish the connection)
        if (!$driver->testAdminConnection($fullCredentials)) {
            throw new Exception('Failed to connect to database with admin credentials');
        }

        return $driver;
    }

    /**
     * Retrieve query logs for JIT account
     */
    public function retrieveQueryLogs(AssetAccount $account, Session $session, ?DatabaseDriverInterface $driver = null): array
    {
        if (!$driver) {
            $driver = $this->getDatabaseDriver($session->asset);
        }

        return $driver->retrieveUserQueryLogs(
            $account->username,
            $session->start_datetime ?? $session->created_at,
            $session->end_datetime ?? now()
        );
    }

    /**
     * Store audit logs in database
     */
    private function storeAuditLogs(Session $session, array $queryLogs): void
    {
        $auditData = [];

        foreach ($queryLogs as $log) {
            $auditData[] = [
                'org_id' => $session->org_id,
                'session_id' => $session->id,
                'request_id' => $session->request_id,
                'asset_id' => $session->asset_id,
                'user_id' => $session->requester_id,
                'query_text' => $log['query_text'] ?? '',
                'query_timestamp' => $log['timestamp'] ?? now(),
                'created_at' => now(),
            ];
        }

        if (!empty($auditData)) {
            SessionAudit::insert($auditData);
            Log::info('Stored session audit logs', [
                'session_id' => $session->id,
                'count' => count($auditData),
            ]);
        }
    }

    /**
     * Validate if a scope is supported for the asset's DBMS
     */
    public function validateScope(Asset $asset, RequestScope $scope): bool
    {
        try {
            $driver = $this->getDatabaseDriver($asset);
            return $driver->validateScope($scope);
        } catch (Exception $e) {
            Log::error('Failed to validate scope', [
                'asset_id' => $asset->id,
                'scope' => $scope->value,
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
        $expiredAccounts = AssetAccount::where('type', AssetAccountType::JIT)
            ->where('expires_at', '<', now())
            ->where('is_active', true)
            ->with('asset')
            ->get();

        $cleaned = 0;

        foreach ($expiredAccounts as $account) {
            try {
                $adminCreds = $this->getAdminCredentials($account->asset);
                $driver = $this->getDatabaseDriver($account->asset, $adminCreds);
                $database = $adminCreds['database'] ?? $account->asset->name;
                $driver->terminateUser($account->username, $database);

                $account->update(['is_active' => false]);
                $account->delete();

                $cleaned++;

            } catch (Exception $e) {
                Log::error('Failed to cleanup expired account', [
                    'account_id' => $account->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Cleaned up expired JIT accounts', ['count' => $cleaned]);
        return $cleaned;
    }
}
