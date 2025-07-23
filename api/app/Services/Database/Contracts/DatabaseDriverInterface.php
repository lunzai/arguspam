<?php

namespace App\Services\Database\Contracts;

use Carbon\Carbon;

interface DatabaseDriverInterface
{
    /**
     * Create a new database user with specified permissions
     */
    public function createUser(string $username, string $password, string $database, string $scope, Carbon $expiresAt): bool;

    /**
     * Terminate/drop a database user
     */
    public function terminateUser(string $username, string $database): bool;

    /**
     * Test admin connection to the database
     */
    public function testAdminConnection(array $adminCredentials): bool;

    /**
     * Retrieve query logs for a specific user
     */
    public function retrieveUserQueryLogs(string $username, Carbon $fromTime, Carbon $toTime): array;

    /**
     * Validate if the scope is supported by this driver
     */
    public function validateScope(string $scope): bool;

    /**
     * Generate secure credentials (username and password)
     */
    public function generateSecureCredentials(): array;
}
