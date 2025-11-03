<?php

namespace App\Services\Jit\Databases\Drivers;

use App\Enums\DatabaseScope;
use App\Services\Jit\Databases\Contracts\DatabaseDriverInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDO;

abstract class AbstractDatabaseDriver implements DatabaseDriverInterface
{
    // Class constants for default configuration values
    private const DEFAULT_USERNAME_PREFIX = 'argus';
    private const DEFAULT_USERNAME_LENGTH = 3;
    private const DEFAULT_RANDOM_LENGTH = 5;
    private const DEFAULT_USERNAME_FORMAT = '{prefix}{number}_{random}';
    private const DEFAULT_PASSWORD_LENGTH = 16;
    private const DEFAULT_PASSWORD_LETTERS = true;
    private const DEFAULT_PASSWORD_NUMBERS = true;
    private const DEFAULT_PASSWORD_SYMBOLS = true;
    private const DEFAULT_PASSWORD_SPACES = false;

    protected PDO $connection;
    protected array $config;
    protected array $supportedScopes = [
        DatabaseScope::READ_ONLY,
        DatabaseScope::READ_WRITE,
        DatabaseScope::DDL,
        DatabaseScope::ALL,
    ];

    /**
     * Normalize database parameter to array format
     */
    protected function normalizeDatabases(string|array $databases): array
    {
        return is_array($databases) ? $databases : [$databases];
    }

    /**
     * Check if user should have access to all databases
     */
    protected function hasAllDatabaseAccess(string|array $databases): bool
    {
        return empty($this->normalizeDatabases($databases));
    }

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function generateUsername(): string
    {
        $numLength = config('pam.jit.username_length', self::DEFAULT_USERNAME_LENGTH);
        $randomLength = config('pam.jit.random_length', self::DEFAULT_RANDOM_LENGTH);
        $number = random_int(10 ** ($numLength - 1), (10 ** $numLength) - 1);
        $random = strtolower(Str::random($randomLength));
        $prefix = config('pam.jit.username_prefix', self::DEFAULT_USERNAME_PREFIX);
        $usernameFormat = config('pam.jit.username_format', self::DEFAULT_USERNAME_FORMAT);
        $username = str_replace(['{prefix}', '{number}', '{random}'], [$prefix, $number, $random], $usernameFormat);
        return $username;
    }

    public function generatePassword(): string
    {
        $length = config('pam.jit.password_length', self::DEFAULT_PASSWORD_LENGTH);
        $letters = config('pam.jit.password_letters', self::DEFAULT_PASSWORD_LETTERS);
        $numbers = config('pam.jit.password_numbers', self::DEFAULT_PASSWORD_NUMBERS);
        $symbols = config('pam.jit.password_symbols', self::DEFAULT_PASSWORD_SYMBOLS);
        $spaces = config('pam.jit.password_spaces', self::DEFAULT_PASSWORD_SPACES);
        return Str::password($length, $letters, $numbers, $symbols, $spaces);
    }

    public function validateScope(DatabaseScope $scope): bool
    {
        return in_array($scope, $this->supportedScopes);
    }

    public function testAdminConnection(array $adminCredentials): bool
    {
        try {
            $this->connect($adminCredentials);
            // Keep the connection alive for subsequent operations
            return true;
        } catch (Exception $e) {
            Log::error('Failed to test admin connection', [
                'driver' => class_basename($this),
                'host' => $adminCredentials['host'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    abstract protected function connect(array $credentials): void;

    abstract protected function getDsn(array $credentials): string;

    protected function handleError(string $operation, Exception $e, array $context = []): void
    {
        Log::error("Database operation failed: {$operation}", array_merge([
            'driver' => class_basename($this),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ], $context));

        throw new \RuntimeException(
            "Database operation failed: {$operation}. Error: ".$e->getMessage(),
            0,
            $e
        );
    }

    public function disconnect(): void
    {
        if (isset($this->connection)) {
            unset($this->connection);
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
