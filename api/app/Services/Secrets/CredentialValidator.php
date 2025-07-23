<?php

namespace App\Services\Secrets;

use App\Models\Asset;
use App\Services\Database\DatabaseDriverFactory;
use Exception;

class CredentialValidator
{
    private SecretsManager $secretsManager;

    public function __construct(SecretsManager $secretsManager)
    {
        $this->secretsManager = $secretsManager;
    }

    /**
     * Validate admin credentials for an asset
     */
    public function validateAdminCredentials(Asset $asset): array
    {
        $result = [
            'valid' => false,
            'message' => '',
            'details' => [],
        ];

        try {
            // Get admin credentials
            $adminCreds = $this->secretsManager->getAdminCredentials($asset);

            // Create driver and test connection
            $driver = DatabaseDriverFactory::create($asset, $adminCreds, $this->secretsManager->config);

            if ($driver->testAdminConnection($adminCreds)) {
                $result['valid'] = true;
                $result['message'] = 'Admin credentials are valid';
                $result['details'] = [
                    'host' => $asset->host,
                    'port' => $asset->port,
                    'dbms' => $asset->dbms->value,
                    'username' => $adminCreds['username'],
                ];
            } else {
                $result['message'] = 'Failed to connect with admin credentials';
            }

        } catch (Exception $e) {
            $result['message'] = 'Validation failed: '.$e->getMessage();
            $result['details']['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Test JIT account creation and termination
     */
    public function testJitLifecycle(Asset $asset): array
    {
        $result = [
            'success' => false,
            'steps' => [],
            'errors' => [],
        ];

        try {
            // Step 1: Get admin credentials
            $result['steps'][] = 'Retrieved admin credentials';
            $adminCreds = $this->secretsManager->getAdminCredentials($asset);

            // Step 2: Create driver
            $result['steps'][] = 'Created database driver';
            $driver = $this->secretsManager->getDatabaseDriver($asset, $adminCreds);

            // Step 3: Generate test credentials
            $result['steps'][] = 'Generated test credentials';
            $testCreds = $driver->generateSecureCredentials();
            $testUsername = 'test_'.$testCreds['username'];

            // Step 4: Create test user
            $result['steps'][] = 'Creating test user';
            $database = $adminCreds['database'] ?? $asset->name;
            $created = $driver->createUser(
                $testUsername,
                $testCreds['password'],
                $database,
                'read_only',
                now()->addMinutes(5)
            );

            if (!$created) {
                throw new Exception('Failed to create test user');
            }
            $result['steps'][] = 'Test user created successfully';

            // Step 5: Terminate test user
            $result['steps'][] = 'Terminating test user';
            $terminated = $driver->terminateUser($testUsername, $database);

            if (!$terminated) {
                throw new Exception('Failed to terminate test user');
            }
            $result['steps'][] = 'Test user terminated successfully';

            $result['success'] = true;

        } catch (Exception $e) {
            $result['errors'][] = $e->getMessage();
        }

        return $result;
    }
}
