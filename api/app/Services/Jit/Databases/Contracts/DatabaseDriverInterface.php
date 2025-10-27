<?php

namespace App\Services\Jit\Databases\Contracts;

use App\Enums\DatabaseScope;
use DateTime;

interface DatabaseDriverInterface
{
    /**
     * Create a new database user with specified permissions
     *
     * @param  string  $username  The username for the new user
     * @param  string  $password  The password for the new user
     * @param  string|array|null  $databases  Database name(s) to grant access to. If null, grants access to all databases
     * @param  DatabaseScope  $scope  The permission scope for the user
     * @param  DateTime  $expiresAt  When the user account expires
     */
    public function createUser(string $username, string $password, string|array $databases, DatabaseScope $scope, DateTime $expiresAt): bool;

    /**
     * Terminate/drop a database user
     */
    public function terminateUser(string $username, string|array $databases): bool;

    /**
     * Test admin connection to the database
     */
    public function testAdminConnection(array $adminCredentials): bool;

    /**
     * Test connection with provided credentials
     */
    public function testConnection(array $credentials): bool;

    /**
     * Retrieve query logs for a specific user
     */
    public function retrieveUserQueryLogs(string $username): array;

    public function isQueryLoggingEnabled(): bool;

    public function enableQueryLogging(): void;

    public function disableQueryLogging(): void;

    /**
     * Validate if the scope is supported by this driver
     */
    public function validateScope(DatabaseScope $scope): bool;

    /**
     * Generate a username
     */
    public function generateUsername(): string;

    /**
     * Generate a password
     */
    public function generatePassword(): string;

    /**
     * Get all databases
     */
    public function getAllDatabases(): array;

}
