<?php

namespace App\Services\Database;

use App\Enums\Dbms;
use App\Models\Asset;
use App\Services\Database\Contracts\DatabaseDriverInterface;
use App\Services\Database\Drivers\MySQLDriver;
use App\Services\Database\Drivers\PostgreSQLDriver;
use InvalidArgumentException;

class DatabaseDriverFactory
{
    public static function create(Asset $asset, array $credentials, array $config): DatabaseDriverInterface
    {
        $config = array_merge($config, [
            'db' => [
                'host' => $asset->host,
                'port' => $asset->port,
                'database' => $credentials['database'] ?? null,
                'username' => $credentials['username'],
                'password' => $credentials['password'],
            ],
        ]);

        return match ($asset->dbms) {
            Dbms::MYSQL => new MySQLDriver($config),
            Dbms::POSTGRESQL => new PostgreSQLDriver($config),
            default => throw new InvalidArgumentException("Unsupported DBMS: {$asset->dbms->value}"),
        };
    }
}
