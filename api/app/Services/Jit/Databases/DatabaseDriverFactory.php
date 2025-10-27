<?php

namespace App\Services\Jit\Databases;

use App\Enums\Dbms;
use App\Models\Asset;
use App\Services\Jit\Databases\Contracts\DatabaseDriverInterface;
use App\Services\Jit\Databases\Drivers\MySQLDriver;
use App\Services\Jit\Databases\Drivers\PostgreSQLDriver;
use InvalidArgumentException;

class DatabaseDriverFactory
{
    public function create(Asset $asset, array $credentials, array $config): DatabaseDriverInterface
    {
        // Determine database name for connection
        $databaseName = $this->determineDatabaseName($credentials, $asset);

        if (empty($databaseName)) {
            throw new InvalidArgumentException('Database name is required but not provided in credentials');
        }

        $config = array_merge($config, [
            'db' => [
                'host' => $asset->host,
                'port' => $asset->port,
                'database' => $databaseName,
                'username' => $credentials['username'],
                'password' => $credentials['password'],
            ],
        ]);

        return match ($asset->dbms) {
            Dbms::MYSQL => new MySQLDriver($config),
            Dbms::POSTGRESQL => new PostgreSQLDriver($config),
            default => throw new InvalidArgumentException('Unsupported DBMS: '.(is_object($asset->dbms) ? $asset->dbms->value : $asset->dbms)),
        };
    }

    /**
     * Determine database name for connection from credentials
     * Priority: credentials['database'] > credentials['databases'][0] > asset default by DBMS
     */
    private function determineDatabaseName(array $credentials, Asset $asset): ?string
    {
        // Check for single database field (backward compatibility)
        if (!empty($credentials['database'])) {
            return $credentials['database'];
        }

        // Check for databases array
        if (!empty($credentials['databases']) && is_array($credentials['databases'])) {
            return $credentials['databases'][0]; // Use first database for connection
        }

        // Default connection database based on DBMS (for "all databases" access)
        return match ($asset->dbms) {
            Dbms::MYSQL => 'mysql',
            Dbms::POSTGRESQL => 'postgres',
            default => null,
        };
    }
}
