<?php

namespace App\Services\Database\Drivers;

use App\Services\Database\Contracts\DatabaseDriverInterface;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PDO;

abstract class AbstractDatabaseDriver implements DatabaseDriverInterface
{
    protected PDO $connection;
    protected array $config;
    protected array $supportedScopes = ['read', 'write', 'admin'];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function generateSecureCredentials(): array
    {
        $username = $this->generateUsername();
        $password = $this->generatePassword();
        return [
            'username' => $username,
            'password' => $password,
        ];
    }

    public function generateUsername(): string
    {
        $numLength = $this->config['jit']['num_length'] ?? 3;
        $randomLength = $this->config['jit']['random_length'] ?? 5;
        $number = random_int(10 ** ($numLength - 1), (10 ** $numLength) - 1);
        $random = strtolower(Str::random($randomLength));
        $prefix = $this->config['jit']['prefix'] ?? 'argus';
        $usernameFormat = $this->config['jit']['username_format'] ?? '{prefix}{number}_{random}';
        $username = str_replace(['{prefix}', '{number}', '{random}'], [$prefix, $number, $random], $usernameFormat);
        return $username;
    }

    public function generatePassword(): string
    {
        $length = $this->config['jit']['password_length'] ?? 16;
        $letters = $this->config['jit']['password_letters'] ?? true;
        $numbers = $this->config['jit']['password_numbers'] ?? true;
        $symbols = $this->config['jit']['password_symbols'] ?? true;
        $spaces = $this->config['jit']['password_spaces'] ?? false;
        return Str::password($length, $letters, $numbers, $symbols, $spaces);
    }

    public function validateScope(string $scope): bool
    {
        return in_array($scope, $this->supportedScopes);
    }

    public function testAdminConnection(array $adminCredentials): bool
    {
        try {
            $this->connect($adminCredentials);
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

    protected function logOperation(string $operation, array $context = []): void
    {
        Log::info("Database operation: {$operation}", array_merge([
            'driver' => class_basename($this),
            'host' => $this->config['host'] ?? 'unknown',
        ], $context));
    }

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

}
