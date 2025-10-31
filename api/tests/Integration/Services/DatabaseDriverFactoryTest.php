<?php

namespace Tests\Unit\Services;

use App\Enums\Dbms;
use App\Models\Asset;
use App\Services\Jit\Database\DatabaseDriverFactory;
use App\Services\Jit\Database\Drivers\MySQLDriver;
use App\Services\Jit\Database\Drivers\PostgreSQLDriver;
use Tests\TestCase;

class DatabaseDriverFactoryTest extends TestCase
{
    private DatabaseDriverFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new DatabaseDriverFactory;
    }

    public function test_it_creates_mysql_driver_with_single_database()
    {
        // Arrange
        $asset = Asset::factory()->create(['dbms' => Dbms::MYSQL]);
        $credentials = [
            'username' => 'test_user',
            'password' => 'test_password',
            'database' => 'test_db',
        ];
        $config = config('pam.database', []);

        // Act
        $driver = $this->factory->create($asset, $credentials, $config);

        // Assert
        $this->assertInstanceOf(MySQLDriver::class, $driver);
    }

    public function test_it_creates_postgresql_driver_with_databases_array()
    {
        // Arrange
        $asset = Asset::factory()->create(['dbms' => Dbms::POSTGRESQL]);
        $credentials = [
            'username' => 'test_user',
            'password' => 'test_password',
            'databases' => ['db1', 'db2'],
        ];
        $config = config('pam.database', []);

        // Act
        $driver = $this->factory->create($asset, $credentials, $config);

        // Assert
        $this->assertInstanceOf(PostgreSQLDriver::class, $driver);
    }

    public function test_it_uses_first_database_from_array_for_connection()
    {
        // Arrange
        $asset = Asset::factory()->create(['dbms' => Dbms::MYSQL]);
        $credentials = [
            'username' => 'test_user',
            'password' => 'test_password',
            'databases' => ['primary_db', 'secondary_db'],
        ];
        $config = config('pam.database', []);

        // Act
        $driver = $this->factory->create($asset, $credentials, $config);

        // Assert
        $this->assertInstanceOf(MySQLDriver::class, $driver);
    }

    public function test_it_uses_dbms_default_when_no_database_specified()
    {
        // Arrange
        $asset = Asset::factory()->create(['dbms' => Dbms::MYSQL]);
        $credentials = [
            'username' => 'test_user',
            'password' => 'test_password',
            // No database specified - should use default
        ];
        $config = config('pam.database', []);

        // Act
        $driver = $this->factory->create($asset, $credentials, $config);

        // Assert
        $this->assertInstanceOf(MySQLDriver::class, $driver);
    }

    public function test_it_throws_exception_for_unsupported_dbms()
    {
        // Arrange
        $asset = new class extends Asset
        {
            public function getAttribute($key)
            {
                return match ($key) {
                    'dbms' => 'UNSUPPORTED',
                    'host' => 'localhost',
                    'port' => 3306,
                    default => parent::getAttribute($key),
                };
            }
        };

        $credentials = [
            'username' => 'test_user',
            'password' => 'test_password',
            'database' => 'test_db', // Provide database to avoid early exception
        ];
        $config = config('pam.database', []);

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported DBMS: UNSUPPORTED');

        // Act
        $this->factory->create($asset, $credentials, $config);
    }

    public function test_it_throws_exception_when_no_database_can_be_determined()
    {
        // Arrange
        $asset = new class extends Asset
        {
            public function getAttribute($key)
            {
                return match ($key) {
                    'dbms' => 'UNKNOWN',
                    'host' => 'localhost',
                    'port' => 3306,
                    default => parent::getAttribute($key),
                };
            }
        };

        $credentials = [
            'username' => 'test_user',
            'password' => 'test_password',
            // No database specified and no default for unknown DBMS
        ];
        $config = config('pam.database', []);

        // Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Database name is required but not provided in credentials');

        // Act
        $this->factory->create($asset, $credentials, $config);
    }
}
