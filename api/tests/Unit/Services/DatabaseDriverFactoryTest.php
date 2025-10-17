<?php

namespace Tests\Unit\Services;

use App\Enums\Dbms;
use App\Models\Asset;
use App\Models\Org;
use App\Services\Database\Contracts\DatabaseDriverInterface;
use App\Services\Database\DatabaseDriverFactory;
use App\Services\Database\Drivers\MySQLDriver;
use App\Services\Database\Drivers\PostgreSQLDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class DatabaseDriverFactoryTest extends TestCase
{
    use RefreshDatabase;

    protected Org $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->org = Org::factory()->create();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_mysql_driver_for_mysql_dbms(): void
    {
        // Arrange
        $asset = Asset::factory()->create([
            'org_id' => $this->org->id,
            'host' => 'mysql.example.com',
            'port' => 3306,
            'dbms' => Dbms::MYSQL,
        ]);

        $credentials = [
            'username' => 'mysql_user',
            'password' => 'mysql_pass',
            'database' => 'mysql_db',
        ];

        $config = [
            'jit' => [
                'prefix' => 'test',
            ],
        ];

        // Act
        $driver = DatabaseDriverFactory::create($asset, $credentials, $config);

        // Assert
        $this->assertInstanceOf(MySQLDriver::class, $driver);
        $this->assertInstanceOf(DatabaseDriverInterface::class, $driver);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_postgresql_driver_for_postgresql_dbms(): void
    {
        // Arrange
        $asset = Asset::factory()->create([
            'org_id' => $this->org->id,
            'host' => 'postgres.example.com',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL,
        ]);

        $credentials = [
            'username' => 'pg_user',
            'password' => 'pg_pass',
            'database' => 'pg_db',
        ];

        $config = [
            'jit' => [
                'prefix' => 'test',
            ],
        ];

        // Act
        $driver = DatabaseDriverFactory::create($asset, $credentials, $config);

        // Assert
        $this->assertInstanceOf(PostgreSQLDriver::class, $driver);
        $this->assertInstanceOf(DatabaseDriverInterface::class, $driver);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_for_unsupported_dbms(): void
    {
        // Arrange
        $asset = Asset::factory()->create([
            'org_id' => $this->org->id,
            'host' => 'db.example.com',
            'port' => 1433,
            'dbms' => Dbms::SQLSERVER, // Unsupported
        ]);

        $credentials = [
            'username' => 'user',
            'password' => 'pass',
        ];

        $config = ['jit' => []];

        // Assert
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported DBMS: sqlserver');

        // Act
        DatabaseDriverFactory::create($asset, $credentials, $config);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_merges_credentials_with_config_correctly(): void
    {
        // Arrange
        $asset = Asset::factory()->create([
            'org_id' => $this->org->id,
            'host' => 'localhost',
            'port' => 3306,
            'dbms' => Dbms::MYSQL,
        ]);

        $credentials = [
            'username' => 'test_user',
            'password' => 'test_pass',
            'database' => 'test_db',
        ];

        $config = [
            'jit' => [
                'prefix' => 'argus',
                'num_length' => 3,
            ],
        ];

        // Act
        $driver = DatabaseDriverFactory::create($asset, $credentials, $config);

        // Assert
        $this->assertInstanceOf(MySQLDriver::class, $driver);

        // Use reflection to verify config was merged
        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);
        $mergedConfig = $property->getValue($driver);

        $this->assertArrayHasKey('jit', $mergedConfig);
        $this->assertArrayHasKey('db', $mergedConfig);
        $this->assertEquals('localhost', $mergedConfig['db']['host']);
        $this->assertEquals(3306, $mergedConfig['db']['port']);
        $this->assertEquals('test_user', $mergedConfig['db']['username']);
        $this->assertEquals('test_pass', $mergedConfig['db']['password']);
        $this->assertEquals('test_db', $mergedConfig['db']['database']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_missing_database_in_credentials(): void
    {
        // Arrange
        $asset = Asset::factory()->create([
            'org_id' => $this->org->id,
            'host' => 'localhost',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL,
        ]);

        $credentials = [
            'username' => 'pg_user',
            'password' => 'pg_pass',
            // No 'database' key
        ];

        $config = ['jit' => []];

        // Act
        $driver = DatabaseDriverFactory::create($asset, $credentials, $config);

        // Assert
        $this->assertInstanceOf(PostgreSQLDriver::class, $driver);

        // Verify database is null
        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);
        $mergedConfig = $property->getValue($driver);

        $this->assertNull($mergedConfig['db']['database']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_driver_with_empty_config(): void
    {
        // Arrange
        $asset = Asset::factory()->create([
            'org_id' => $this->org->id,
            'host' => 'localhost',
            'port' => 3306,
            'dbms' => Dbms::MYSQL,
        ]);

        $credentials = [
            'username' => 'user',
            'password' => 'pass',
        ];

        $config = []; // Empty config

        // Act
        $driver = DatabaseDriverFactory::create($asset, $credentials, $config);

        // Assert
        $this->assertInstanceOf(MySQLDriver::class, $driver);

        // Verify config still has db key
        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);
        $mergedConfig = $property->getValue($driver);

        $this->assertArrayHasKey('db', $mergedConfig);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_preserves_original_config_keys(): void
    {
        // Arrange
        $asset = Asset::factory()->create([
            'org_id' => $this->org->id,
            'host' => 'localhost',
            'port' => 3306,
            'dbms' => Dbms::MYSQL,
        ]);

        $credentials = [
            'username' => 'user',
            'password' => 'pass',
        ];

        $config = [
            'jit' => [
                'prefix' => 'argus',
                'password_length' => 16,
            ],
            'audit' => [
                'retain_days' => 90,
            ],
        ];

        // Act
        $driver = DatabaseDriverFactory::create($asset, $credentials, $config);

        // Assert
        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);
        $mergedConfig = $property->getValue($driver);

        $this->assertArrayHasKey('jit', $mergedConfig);
        $this->assertArrayHasKey('audit', $mergedConfig);
        $this->assertArrayHasKey('db', $mergedConfig);
        $this->assertEquals('argus', $mergedConfig['jit']['prefix']);
        $this->assertEquals(90, $mergedConfig['audit']['retain_days']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_uses_asset_host_and_port_in_config(): void
    {
        // Arrange
        $asset = Asset::factory()->create([
            'org_id' => $this->org->id,
            'host' => '192.168.1.100',
            'port' => 3307,
            'dbms' => Dbms::MYSQL,
        ]);

        $credentials = [
            'username' => 'user',
            'password' => 'pass',
        ];

        $config = ['jit' => []];

        // Act
        $driver = DatabaseDriverFactory::create($asset, $credentials, $config);

        // Assert
        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);
        $mergedConfig = $property->getValue($driver);

        $this->assertEquals('192.168.1.100', $mergedConfig['db']['host']);
        $this->assertEquals(3307, $mergedConfig['db']['port']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_uses_credentials_username_and_password(): void
    {
        // Arrange
        $asset = Asset::factory()->create([
            'org_id' => $this->org->id,
            'host' => 'localhost',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL,
        ]);

        $credentials = [
            'username' => 'special_user',
            'password' => 'SuperSecret123!',
            'database' => 'special_db',
        ];

        $config = ['jit' => []];

        // Act
        $driver = DatabaseDriverFactory::create($asset, $credentials, $config);

        // Assert
        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);
        $mergedConfig = $property->getValue($driver);

        $this->assertEquals('special_user', $mergedConfig['db']['username']);
        $this->assertEquals('SuperSecret123!', $mergedConfig['db']['password']);
        $this->assertEquals('special_db', $mergedConfig['db']['database']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_is_a_static_factory_method(): void
    {
        // Arrange
        $asset = Asset::factory()->create([
            'org_id' => $this->org->id,
            'dbms' => Dbms::MYSQL,
        ]);

        $credentials = [
            'username' => 'user',
            'password' => 'pass',
        ];

        // Act
        $driver = DatabaseDriverFactory::create($asset, $credentials, []);

        // Assert
        $this->assertInstanceOf(DatabaseDriverInterface::class, $driver);
        // Verify the method is static by checking it can be called without instantiation
        $reflection = new \ReflectionMethod(DatabaseDriverFactory::class, 'create');
        $this->assertTrue($reflection->isStatic());
    }
}
