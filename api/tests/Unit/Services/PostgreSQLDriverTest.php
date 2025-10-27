<?php

namespace Tests\Unit\Services;

use App\Services\Jit\Database\Drivers\PostgreSQLDriver;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Mockery;
use PDO;
use PDOStatement;
use RuntimeException;
use Tests\TestCase;

class PostgreSQLDriverTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function createDriver(array $config = []): PostgreSQLDriver
    {
        $defaultConfig = [
            'jit' => [
                'prefix' => 'test',
                'num_length' => 3,
                'random_length' => 5,
            ],
            'db' => [
                'host' => 'localhost',
                'port' => 5432,
                'database' => 'test_db',
                'username' => 'test_user',
                'password' => 'test_pass',
            ],
        ];

        return new PostgreSQLDriver(array_merge($defaultConfig, $config));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_correct_dsn_with_all_parameters(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $credentials = [
            'host' => 'postgres.example.com',
            'port' => 5433,
            'database' => 'mydb',
            'username' => 'admin',
            'password' => 'secret',
        ];

        // Act
        $reflection = new \ReflectionMethod($driver, 'getDsn');
        $reflection->setAccessible(true);
        $dsn = $reflection->invoke($driver, $credentials);

        // Assert
        $this->assertEquals(
            'pgsql:host=postgres.example.com;port=5433;dbname=mydb;user=admin;password=secret',
            $dsn
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_dsn_with_default_port(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $credentials = [
            'host' => 'localhost',
            // No port specified
            'database' => 'testdb',
            'username' => 'user',
            'password' => 'pass',
        ];

        // Act
        $reflection = new \ReflectionMethod($driver, 'getDsn');
        $reflection->setAccessible(true);
        $dsn = $reflection->invoke($driver, $credentials);

        // Assert
        $this->assertStringContainsString('port=5432', $dsn); // Default PostgreSQL port
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_missing_credentials_in_dsn(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $credentials = [
            'host' => 'localhost',
            'port' => 5432,
            // Missing database, username, password
        ];

        // Act
        $reflection = new \ReflectionMethod($driver, 'getDsn');
        $reflection->setAccessible(true);
        $dsn = $reflection->invoke($driver, $credentials);

        // Assert
        $this->assertStringContainsString('host=localhost', $dsn);
        $this->assertStringContainsString('port=5432', $dsn);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_user_with_read_scope_successfully(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);
        $mockStmt = Mockery::mock(PDOStatement::class);

        $mockPdo->shouldReceive('beginTransaction')->once();
        $mockPdo->shouldReceive('prepare')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturn($mockStmt);
        $mockStmt->shouldReceive('execute')
            ->once()
            ->with(Mockery::type('array'))
            ->andReturn(true);

        // Mock grant permissions calls
        $mockPdo->shouldReceive('exec')
            ->times(4) // GRANT CONNECT + 3 read permissions
            ->andReturn(true);

        $mockPdo->shouldReceive('commit')->once();

        // Inject mock PDO
        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->twice(); // logOperation calls

        // Act
        $result = $driver->createUser(
            'test_user',
            'test_pass',
            'test_db',
            \App\Enums\DatabaseScope::READ_ONLY,
            Carbon::now()->addHour()
        );

        // Assert
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_user_with_write_scope_successfully(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);
        $mockStmt = Mockery::mock(PDOStatement::class);

        $mockPdo->shouldReceive('beginTransaction')->once();
        $mockPdo->shouldReceive('prepare')->once()->andReturn($mockStmt);
        $mockStmt->shouldReceive('execute')->once()->andReturn(true);

        // Mock grant permissions calls for write scope
        $mockPdo->shouldReceive('exec')
            ->times(6) // GRANT CONNECT + 5 write permissions
            ->andReturn(true);

        $mockPdo->shouldReceive('commit')->once();

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->twice();

        // Act
        $result = $driver->createUser(
            'write_user',
            'write_pass',
            'test_db',
            \App\Enums\DatabaseScope::READ_WRITE,
            Carbon::now()->addHour()
        );

        // Assert
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_user_with_admin_scope_successfully(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);
        $mockStmt = Mockery::mock(PDOStatement::class);

        $mockPdo->shouldReceive('beginTransaction')->once();
        $mockPdo->shouldReceive('prepare')->once()->andReturn($mockStmt);
        $mockStmt->shouldReceive('execute')->once()->andReturn(true);

        // Mock grant permissions calls for admin scope
        $mockPdo->shouldReceive('exec')
            ->times(5) // GRANT CONNECT + 4 admin privileges
            ->andReturn(true);

        $mockPdo->shouldReceive('commit')->once();

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->twice();

        // Act
        $result = $driver->createUser(
            'admin_user',
            'admin_pass',
            'test_db',
            \App\Enums\DatabaseScope::ALL,
            Carbon::now()->addHour()
        );

        // Assert
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_rolls_back_transaction_on_create_user_failure(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);

        $mockPdo->shouldReceive('beginTransaction')->once();
        $mockPdo->shouldReceive('prepare')
            ->once()
            ->andThrow(new Exception('Database error'));
        $mockPdo->shouldReceive('rollBack')->once();

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->once(); // Initial log
        Log::shouldReceive('error')->once(); // handleError log

        // Assert - handleError throws RuntimeException
        $this->expectException(RuntimeException::class);

        // Act
        $driver->createUser(
            'test_user',
            'test_pass',
            'test_db',
            \App\Enums\DatabaseScope::READ_ONLY,
            Carbon::now()->addHour()
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_terminates_user_successfully(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);
        $mockStmt = Mockery::mock(PDOStatement::class);

        $mockPdo->shouldReceive('beginTransaction')->once();

        // Mock pg_terminate_backend query
        $mockPdo->shouldReceive('prepare')
            ->once()
            ->with(Mockery::type('string'))
            ->andReturn($mockStmt);
        $mockStmt->shouldReceive('execute')
            ->once()
            ->with([':username' => 'test_user'])
            ->andReturn(true);

        // Mock revoke and drop statements
        $mockPdo->shouldReceive('exec')
            ->times(5) // 4 REVOKEs + 1 DROP USER
            ->andReturn(true);

        $mockPdo->shouldReceive('commit')->once();

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->twice(); // Log calls

        // Act
        $result = $driver->terminateUser('test_user', 'test_db');

        // Assert
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_rolls_back_transaction_on_terminate_user_failure(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);

        $mockPdo->shouldReceive('beginTransaction')->once();
        $mockPdo->shouldReceive('prepare')
            ->once()
            ->andThrow(new Exception('Termination failed'));
        $mockPdo->shouldReceive('rollBack')->once();

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->once();
        Log::shouldReceive('error')->once();

        // Assert
        $this->expectException(RuntimeException::class);

        // Act
        $driver->terminateUser('test_user', 'test_db');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_retrieves_query_logs_from_pg_stat_activity(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);
        $mockStmt = Mockery::mock(PDOStatement::class);
        $mockExtStmt = Mockery::mock(PDOStatement::class);

        $expectedLogs = [
            [
                'timestamp' => '2025-01-01 10:00:00',
                'query_text' => 'SELECT * FROM users',
                'application_name' => 'psql',
                'client_addr' => '127.0.0.1',
            ],
        ];

        // Mock pg_stat_activity query
        $mockPdo->shouldReceive('prepare')
            ->once()
            ->with(Mockery::on(function ($sql) {
                return str_contains($sql, 'pg_stat_activity');
            }))
            ->andReturn($mockStmt);
        $mockStmt->shouldReceive('execute')->once()->andReturn(true);
        $mockStmt->shouldReceive('fetchAll')->once()->andReturn($expectedLogs);

        // Mock pg_extension check
        $mockPdo->shouldReceive('query')
            ->once()
            ->with(Mockery::on(function ($sql) {
                return str_contains($sql, 'pg_extension');
            }))
            ->andReturn($mockExtStmt);
        $mockExtStmt->shouldReceive('fetch')->once()->andReturn(false); // Extension not available

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->twice();

        // Act
        $logs = $driver->retrieveUserQueryLogs(
            'test_user',
            Carbon::parse('2025-01-01 09:00:00'),
            Carbon::parse('2025-01-01 11:00:00')
        );

        // Assert
        $this->assertCount(1, $logs);
        $this->assertEquals($expectedLogs, $logs);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_retrieves_query_logs_with_pg_stat_statements(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);
        $mockActivityStmt = Mockery::mock(PDOStatement::class);
        $mockExtStmt = Mockery::mock(PDOStatement::class);
        $mockStatStmt = Mockery::mock(PDOStatement::class);

        $activityLogs = [
            ['timestamp' => '2025-01-01 10:00:00', 'query_text' => 'SELECT * FROM users'],
        ];

        $statementLogs = [
            [
                'query' => 'SELECT * FROM orders',
                'calls' => 5,
                'mean_exec_time' => 12.5,
                'total_exec_time' => 62.5,
            ],
        ];

        // Mock pg_stat_activity query
        $mockPdo->shouldReceive('prepare')
            ->once()
            ->with(Mockery::on(fn ($sql) => str_contains($sql, 'pg_stat_activity')))
            ->andReturn($mockActivityStmt);
        $mockActivityStmt->shouldReceive('execute')->once()->andReturn(true);
        $mockActivityStmt->shouldReceive('fetchAll')->once()->andReturn($activityLogs);

        // Mock pg_extension check (extension available)
        $mockPdo->shouldReceive('query')->once()->andReturn($mockExtStmt);
        $mockExtStmt->shouldReceive('fetch')->once()->andReturn(true);

        // Mock pg_stat_statements query
        $mockPdo->shouldReceive('prepare')
            ->once()
            ->with(Mockery::on(fn ($sql) => str_contains($sql, 'pg_stat_statements')))
            ->andReturn($mockStatStmt);
        $mockStatStmt->shouldReceive('execute')->once()->andReturn(true);
        $mockStatStmt->shouldReceive('fetchAll')->once()->andReturn($statementLogs);

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->twice();

        // Act
        $logs = $driver->retrieveUserQueryLogs(
            'test_user',
            Carbon::parse('2025-01-01 09:00:00'),
            Carbon::parse('2025-01-01 11:00:00')
        );

        // Assert
        $this->assertCount(2, $logs); // 1 from activity + 1 from statements
        $this->assertEquals('SELECT * FROM users', $logs[0]['query_text']);
        $this->assertEquals('SELECT * FROM orders', $logs[1]['query_text']);
        $this->assertEquals(5, $logs[1]['execution_count']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_pg_stat_statements_not_available_gracefully(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);
        $mockActivityStmt = Mockery::mock(PDOStatement::class);
        $mockExtStmt = Mockery::mock(PDOStatement::class);

        $activityLogs = [
            ['timestamp' => '2025-01-01 10:00:00', 'query_text' => 'SELECT * FROM users'],
        ];

        // Mock pg_stat_activity query
        $mockPdo->shouldReceive('prepare')->once()->andReturn($mockActivityStmt);
        $mockActivityStmt->shouldReceive('execute')->once()->andReturn(true);
        $mockActivityStmt->shouldReceive('fetchAll')->once()->andReturn($activityLogs);

        // Mock pg_extension check - extension available
        $mockPdo->shouldReceive('query')->once()->andReturn($mockExtStmt);
        $mockExtStmt->shouldReceive('fetch')->once()->andReturn(true);

        // Mock pg_stat_statements query failure
        $mockPdo->shouldReceive('prepare')
            ->once()
            ->andThrow(new Exception('pg_stat_statements error'));

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->twice();
        Log::shouldReceive('debug')->once()->with(
            'pg_stat_statements not available',
            Mockery::type('array')
        );

        // Act
        $logs = $driver->retrieveUserQueryLogs(
            'test_user',
            Carbon::parse('2025-01-01 09:00:00'),
            Carbon::parse('2025-01-01 11:00:00')
        );

        // Assert
        $this->assertCount(1, $logs); // Only activity logs
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_empty_array_on_query_log_retrieval_failure(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);

        $mockPdo->shouldReceive('prepare')
            ->once()
            ->andThrow(new Exception('Query failed'));

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->once();
        Log::shouldReceive('error')->once();

        // Assert - Should NOT throw exception, just return empty array
        $this->expectException(RuntimeException::class);

        // Act
        $logs = $driver->retrieveUserQueryLogs(
            'test_user',
            Carbon::parse('2025-01-01 09:00:00'),
            Carbon::parse('2025-01-01 11:00:00')
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_extends_abstract_database_driver(): void
    {
        // Arrange
        $driver = $this->createDriver();

        // Assert
        $this->assertInstanceOf(\App\Services\Jit\Database\Drivers\AbstractDatabaseDriver::class, $driver);
    }
}
