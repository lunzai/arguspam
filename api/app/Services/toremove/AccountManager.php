<?php

namespace App\Services\Jit;

use App\Enums\AssetAccountType;
use App\Exceptions\JitAccountException;
use App\Exceptions\ValidationException;
use App\Models\AssetAccount;
use App\Models\Session;
use App\Services\Jit\Database\Contracts\DatabaseDriverInterface;
use App\Services\Jit\Database\DatabaseDriverFactory;
use App\Services\Jit\Repositories\Contracts\AssetRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AccountManager
{
    public function __construct(
        private AssetRepositoryInterface $assetRepository,
        private DatabaseDriverFactory $driverFactory,
        private array $config
    ) {}

    public function createAccount(Session $session, DatabaseDriverInterface $driver, string|array|null $databases): AssetAccount
    {
        Log::info('Creating JIT account for session', ['session_id' => $session->id]);

        try {
            // Generate JIT credentials (outside transaction)
            $jitCreds = $driver->generateSecureCredentials();

            // Create user in database (outside transaction - database operation)
            $success = $driver->createUser(
                $jitCreds['username'],
                $jitCreds['password'],
                $databases,
                $session->request->scope,
                $session->scheduled_end_datetime
            );

            if (!$success) {
                Log::error('Failed to create JIT user in database', [
                    'session_id' => $session->id,
                    'username' => $jitCreds['username'],
                ]);
                throw new JitAccountException('Failed to create JIT user in database');
            }

            // Store JIT account and update session in a single transaction
            return DB::transaction(function () use ($session, $jitCreds, $databases) {
                // Store JIT account in database
                $jitAccount = AssetAccount::create([
                    'asset_id' => $session->asset_id,
                    'name' => "JIT account for session {$session->id}",
                    'username' => $jitCreds['username'],
                    'password' => $jitCreds['password'],  // Model cast handles encryption
                    'databases' => $databases,  // Store what databases this account has access to
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

        } catch (ValidationException $e) {
            Log::error('JIT account validation failed', [
                'session_id' => $session->id,
                'errors' => $e->getErrors(),
            ]);
            throw new JitAccountException(
                'User creation validation failed: '.$e->getMessage(),
                0,
                $e
            );
        } catch (\Exception $e) {
            Log::error('Failed to create JIT account', [
                'session_id' => $session->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new JitAccountException(
                'Failed to create JIT account: '.$e->getMessage(),
                0,
                $e
            );
        }
    }

    public function terminateAccount(Session $session, DatabaseDriverInterface $driver, AssetAccount $jitAccount, array $adminCredentials): void
    {
        Log::info('Terminating JIT account for session', ['session_id' => $session->id]);

        try {
            // Determine database for termination
            $database = $adminCredentials['database'] ?? ($adminCredentials['databases'][0] ?? null);

            if (!$database) {
                Log::error('Cannot determine database for termination', [
                    'session_id' => $session->id,
                    'admin_credentials' => array_keys($adminCredentials),
                ]);
                throw new JitAccountException('Cannot determine database for termination');
            }

            // Terminate user in database
            $terminated = $driver->terminateUser($jitAccount->username, $database);

            if (!$terminated) {
                Log::error('Failed to terminate database user', [
                    'session_id' => $session->id,
                    'username' => $jitAccount->username,
                    'database' => $database,
                ]);
                throw new JitAccountException('Failed to terminate database user');
            }

            // Delete JIT account record
            $jitAccount->delete();

            Log::info('JIT account terminated successfully', [
                'session_id' => $session->id,
                'account_id' => $jitAccount->id,
                'username' => $jitAccount->username,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to terminate JIT account', [
                'session_id' => $session->id,
                'account_id' => $jitAccount->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new JitAccountException(
                'Failed to terminate JIT account: '.$e->getMessage(),
                0,
                $e
            );
        }
    }

    public function cleanupExpiredAccounts(): int
    {
        $expiredAccounts = $this->assetRepository->getExpiredJitAccounts();
        $cleaned = 0;

        foreach ($expiredAccounts as $accountData) {
            try {
                $account = AssetAccount::find($accountData['id']);
                if (!$account) {
                    continue;
                }

                $asset = $this->assetRepository->find($account->asset_id);
                if (!$asset) {
                    continue;
                }

                $adminAccount = $this->assetRepository->getActiveAdminAccount($asset);
                if (!$adminAccount) {
                    Log::warning('No admin account found for asset during cleanup', [
                        'asset_id' => $asset->id,
                        'asset_name' => $asset->name,
                        'account_id' => $account->id,
                    ]);
                    continue;
                }

                // Extract database name from admin credentials
                $database = $adminAccount->database ?? null;
                if (empty($database) && empty($adminAccount->databases)) {
                    Log::warning('No database name specified for asset during cleanup', [
                        'asset_id' => $asset->id,
                        'asset_name' => $asset->name,
                        'account_id' => $account->id,
                    ]);
                    continue;
                }

                // Use first database for connection
                $connectionDatabase = $database ?? ($adminAccount->databases[0] ?? null);
                if (empty($connectionDatabase)) {
                    Log::warning('No valid database name for asset during cleanup', [
                        'asset_id' => $asset->id,
                        'asset_name' => $asset->name,
                        'account_id' => $account->id,
                    ]);
                    continue;
                }

                $driver = $this->driverFactory->create($asset, [
                    'host' => $asset->host,
                    'port' => $asset->port,
                    'database' => $connectionDatabase,
                    'username' => $adminAccount->username,
                    'password' => $adminAccount->password,
                ], $this->config);

                $driver->terminateUser($account->username, $connectionDatabase);
                $account->update(['is_active' => false]);
                $account->delete();

                $cleaned++;

            } catch (\Exception $e) {
                Log::error('Failed to cleanup expired account', [
                    'account_id' => $accountData['id'],
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                // Continue processing other accounts
            }
        }

        Log::info('Cleaned up expired JIT accounts', ['count' => $cleaned]);

        return $cleaned;
    }
}
