<?php

namespace Tests\Integration\Services\Jit;

use App\Enums\Dbms;
use App\Models\Asset;
use App\Services\Jit\Databases\DatabaseDriverFactory;
use App\Services\Jit\Databases\Drivers\MySQLDriver;
use App\Services\Jit\Databases\Drivers\PostgreSQLDriver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use InvalidArgumentException;
use Tests\TestCase;

class DatabaseDriverFactoryTest extends TestCase
{
    use RefreshDatabase;

    private DatabaseDriverFactory $factory;

    private array $credentials;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->factory = new DatabaseDriverFactory;
        $this->credentials = [
            'username' => 'root',
            'password' => 'secret',
            'database' => 'testdb',
        ];
    }

    public function test_creates_mysql_driver_for_mysql_asset(): void
    {
        $asset = Asset::factory()->create(['dbms' => Dbms::MYSQL]);

        $driver = $this->factory->create($asset, $this->credentials, []);

        $this->assertInstanceOf(MySQLDriver::class, $driver);
    }

    public function test_creates_postgresql_driver_for_postgresql_asset(): void
    {
        $asset = Asset::factory()->create(['dbms' => Dbms::POSTGRESQL]);

        $driver = $this->factory->create($asset, $this->credentials, []);

        $this->assertInstanceOf(PostgreSQLDriver::class, $driver);
    }

    public function test_throws_for_unsupported_dbms(): void
    {
        $asset = Asset::factory()->create(['dbms' => Dbms::MONGODB]);

        $this->expectException(InvalidArgumentException::class);
        $this->factory->create($asset, $this->credentials, []);
    }

    public function test_uses_databases_array_when_no_single_database_in_credentials(): void
    {
        $asset = Asset::factory()->create(['dbms' => Dbms::MYSQL]);
        $credentials = [
            'username' => 'root',
            'password' => 'secret',
            'databases' => ['mydb', 'otherdb'],
        ];

        $driver = $this->factory->create($asset, $credentials, []);

        $this->assertInstanceOf(MySQLDriver::class, $driver);
    }

    public function test_uses_dbms_default_database_when_no_database_provided(): void
    {
        $asset = Asset::factory()->create(['dbms' => Dbms::MYSQL]);
        $credentials = [
            'username' => 'root',
            'password' => 'secret',
        ];

        $driver = $this->factory->create($asset, $credentials, []);

        $this->assertInstanceOf(MySQLDriver::class, $driver);
    }

    public function test_throws_when_database_cannot_be_determined_for_unsupported_dbms(): void
    {
        $asset = Asset::factory()->create(['dbms' => Dbms::MONGODB]);
        $credentials = ['username' => 'root', 'password' => 'secret'];

        $this->expectException(InvalidArgumentException::class);
        $this->factory->create($asset, $credentials, []);
    }
}
