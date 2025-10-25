<?php

namespace App\Services\Jit;

use App\Constants\LogMessages;
use App\Exceptions\CredentialNotFoundException;
use App\Exceptions\ValidationException;
use App\Models\Asset;
use App\Services\Jit\Database\DatabaseDriverFactory;
use Illuminate\Support\Facades\Log;

class CredentialManager
{
    public function __construct(
        private DatabaseDriverFactory $driverFactory,
        private array $config
    ) {}

    /**
     * Get admin credentials for an asset
     *
     * @return array{username: string, password: string, databases: array|null}
     *
     * @throws CredentialNotFoundException When no active admin account is found
     * @throws \RuntimeException When an unexpected error occurs
     */
    public function getAdminCredentials(Asset $asset): array
    {
        try {
            $adminAccount = $asset->adminAccount;

            if (!$adminAccount) {
                Log::error(LogMessages::NO_ACTIVE_ADMIN_ACCOUNT, [
                    'asset_id' => $asset->id,
                    'asset_name' => $asset->name,
                ]);
                throw new CredentialNotFoundException("No active admin account found for asset: {$asset->name}");
            }

            // Model's encrypted cast automatically decrypts
            return [
                'username' => $adminAccount->username,
                'password' => $adminAccount->password,
                'databases' => $adminAccount->databases,
            ];

        } catch (\Exception $e) {
            Log::error('Failed to get admin credentials: '.$e->getMessage(), [
                'asset_id' => $asset->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to get admin credentials: '.$e->getMessage(), 0, $e);
        }
    }

    /**
     * Generate secure credentials for a new user
     *
     * @return array{username: string, password: string}
     *
     * @throws ValidationException When generated credentials fail validation
     * @throws \RuntimeException When an unexpected error occurs
     */
    public function generateCredentials(Asset $asset): array
    {
        try {
            // Get admin credentials to determine database name
            $adminCreds = $this->getAdminCredentials($asset);

            $driver = $this->driverFactory->create($asset, [
                'username' => 'temp',
                'password' => 'temp',
                'databases' => $adminCreds['databases'],
            ], $this->config);

            $credentials = $driver->generateSecureCredentials();

            return $credentials;

        } catch (ValidationException $e) {
            Log::error(LogMessages::GENERATED_CREDENTIALS_VALIDATION_FAILED, [
                'asset_id' => $asset->id,
                'errors' => $e->getErrors(),
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('Failed to generate credentials: '.$e->getMessage(), [
                'asset_id' => $asset->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw new \RuntimeException('Failed to generate credentials: '.$e->getMessage(), 0, $e);
        }
    }
}
