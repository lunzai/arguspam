<?php

namespace App\Services\Database\Contracts;

use App\Enums\RequestScope;
use Carbon\Carbon;

interface DatabaseDriverInterface
{
    /**
     * Create a new database user with specified permissions
     * @param string $username The username for the new user
     * @param string $password The password for the new user
     * @param string|array|null $databases Database name(s) to grant access to. If null, grants access to all databases
     * @param RequestScope $scope The permission scope for the user
     * @param Carbon $expiresAt When the user account expires
     */
    public function createUser(string $username, string $password, string|array|null $databases, RequestScope $scope, Carbon $expiresAt): bool;

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
    public function validateScope(RequestScope $scope): bool;

    /**
     * Generate secure credentials (username and password)
     */
    public function generateSecureCredentials(): array;
}
