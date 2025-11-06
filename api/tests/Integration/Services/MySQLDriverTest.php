<?php

namespace Tests\Integration\Services;

use App\Services\Jit\Database\Drivers\MySQLDriver;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use Mockery;
use PDO;
use PDOStatement;
use RuntimeException;
use Tests\TestCase;

class MySQLDriverTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    protected function createDriver(array $config = []): MySQLDriver
    {
        $defaultConfig = [
            'jit' => [
                'prefix' => 'test',
                'num_length' => 3,
                'random_length' => 5,
            ],
            'db' => [
                'host' => 'localhost',
                'port' => 3306,
                'database' => 'test_db',
                'username' => 'test_user',
                'password' => 'test_pass',
            ],
        ];

        return new MySQLDriver(array_merge($defaultConfig, $config));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_correct_dsn_with_all_parameters(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $credentials = [
            'host' => 'mysql.example.com',
            'port' => 3307,
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
            'mysql:host=mysql.example.com;port=3307;dbname=mydb;user=admin;password=secret;charset=utf8mb4',
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
        $this->assertStringContainsString('port=3306', $dsn); // Default MySQL port
        $this->assertStringContainsString('charset=utf8mb4', $dsn);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_user_with_read_only_scope_successfully(): void
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

        // Mock grant permissions + flush privileges
        $mockPdo->shouldReceive('exec')
            ->times(2) // GRANT SELECT + FLUSH PRIVILEGES
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
            Carbon::now()->addDays(7)
        );

        // Assert
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_creates_user_with_read_write_scope_successfully(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);
        $mockStmt = Mockery::mock(PDOStatement::class);

        $mockPdo->shouldReceive('beginTransaction')->once();
        $mockPdo->shouldReceive('prepare')->once()->andReturn($mockStmt);
        $mockStmt->shouldReceive('execute')->once()->andReturn(true);

        // Mock grant permissions for read_write scope + flush
        $mockPdo->shouldReceive('exec')
            ->times(2) // GRANT SELECT,INSERT,UPDATE,DELETE + FLUSH PRIVILEGES
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
            Carbon::now()->addDays(7)
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

        // Mock grant permissions for admin scope + flush
        $mockPdo->shouldReceive('exec')
            ->times(2) // GRANT ALL PRIVILEGES + FLUSH PRIVILEGES
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
            Carbon::now()->addDays(30)
        );

        // Assert
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_calculates_days_until_expiry_correctly(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);
        $mockStmt = Mockery::mock(PDOStatement::class);

        $expiresAt = Carbon::now()->addDays(15);

        $mockPdo->shouldReceive('beginTransaction')->once();
        $mockPdo->shouldReceive('prepare')->once()->andReturn($mockStmt);

        // Verify the days parameter is correctly calculated
        $mockStmt->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(function ($params) {
                return isset($params[':days']) && $params[':days'] >= 14 && $params[':days'] <= 15;
            }))
            ->andReturn(true);

        $mockPdo->shouldReceive('exec')->times(2)->andReturn(true);
        $mockPdo->shouldReceive('commit')->once();

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->twice();

        // Act
        $result = $driver->createUser('test_user', 'pass', 'db', \App\Enums\DatabaseScope::READ_ONLY, $expiresAt);

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
            ->andThrow(new Exception('User creation failed'));
        $mockPdo->shouldReceive('rollBack')->once();

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->once(); // Initial log
        Log::shouldReceive('error')->once(); // handleError log

        // Assert
        $this->expectException(RuntimeException::class);

        // Act
        $driver->createUser('test_user', 'pass', 'db', \App\Enums\DatabaseScope::READ_ONLY, Carbon::now()->addDays(7));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_terminates_user_and_kills_active_connections(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);
        $mockStmt = Mockery::mock(PDOStatement::class);

        $mockPdo->shouldReceive('beginTransaction')->once();

        // Mock processlist query
        $mockPdo->shouldReceive('prepare')
            ->once()
            ->with(Mockery::on(fn ($sql) => str_contains($sql, 'information_schema.processlist')))
            ->andReturn($mockStmt);
        $mockStmt->shouldReceive('execute')
            ->once()
            ->with([':username' => 'test_user'])
            ->andReturn(true);
        $mockStmt->shouldReceive('fetchAll')
            ->once()
            ->with(PDO::FETCH_COLUMN)
            ->andReturn(['KILL 123;', 'KILL 456;']);

        // Mock KILL commands + REVOKE + DROP + FLUSH
        $mockPdo->shouldReceive('exec')
            ->times(5) // 2 KILLs + REVOKE + DROP + FLUSH
            ->andReturn(true);

        $mockPdo->shouldReceive('commit')->once();

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->twice();

        // Act
        $result = $driver->terminateUser('test_user', 'test_db');

        // Assert
        $this->assertTrue($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_continues_termination_if_kill_command_fails(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);
        $mockStmt = Mockery::mock(PDOStatement::class);

        $mockPdo->shouldReceive('beginTransaction')->once();
        $mockPdo->shouldReceive('prepare')->once()->andReturn($mockStmt);
        $mockStmt->shouldReceive('execute')->once()->andReturn(true);
        $mockStmt->shouldReceive('fetchAll')
            ->once()
            ->andReturn(['KILL 123;']);

        // First KILL fails, but continues
        $mockPdo->shouldReceive('exec')
            ->once()
            ->with('KILL 123;')
            ->andThrow(new Exception('Connection already closed'));

        // Should continue with REVOKE, DROP, FLUSH
        $mockPdo->shouldReceive('exec')->times(3)->andReturn(true);
        $mockPdo->shouldReceive('commit')->once();

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->twice();
        Log::shouldReceive('debug')->once()->with(
            'Failed to kill connection',
            Mockery::type('array')
        );

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
    public function it_retrieves_query_logs_from_general_log(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);
        $mockStmt = Mockery::mock(PDOStatement::class);
        $mockLogCheckStmt = Mockery::mock(PDOStatement::class);
        $mockLogOutputStmt = Mockery::mock(PDOStatement::class);

        $expectedLogs = [
            [
                'timestamp' => '2025-01-01 10:00:00',
                'query_text' => 'SELECT * FROM users',
            ],
        ];

        // Mock SHOW VARIABLES checks
        $mockPdo->shouldReceive('query')
            ->once()
            ->with(Mockery::on(fn ($sql) => str_contains($sql, 'general_log')))
            ->andReturn($mockLogCheckStmt);
        $mockLogCheckStmt->shouldReceive('fetch')
            ->once()
            ->andReturn(['Value' => 'ON']);

        $mockPdo->shouldReceive('query')
            ->once()
            ->with(Mockery::on(fn ($sql) => str_contains($sql, 'log_output')))
            ->andReturn($mockLogOutputStmt);
        $mockLogOutputStmt->shouldReceive('fetch')
            ->once()
            ->andReturn(['Value' => 'TABLE']);

        // Mock general_log query
        $mockPdo->shouldReceive('prepare')
            ->once()
            ->with(Mockery::on(fn ($sql) => str_contains($sql, 'mysql.general_log')))
            ->andReturn($mockStmt);
        $mockStmt->shouldReceive('execute')->once()->andReturn(true);
        $mockStmt->shouldReceive('fetchAll')->once()->andReturn($expectedLogs);

        // Mock performance_schema query (will fail)
        $mockPdo->shouldReceive('prepare')
            ->once()
            ->with(Mockery::on(fn ($sql) => str_contains($sql, 'performance_schema')))
            ->andThrow(new Exception('Performance schema not available'));

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->twice();
        Log::shouldReceive('debug')->once();

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
    public function it_skips_general_log_when_disabled(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);
        $mockLogCheckStmt = Mockery::mock(PDOStatement::class);
        $mockLogOutputStmt = Mockery::mock(PDOStatement::class);

        // Mock SHOW VARIABLES for general_log - log is OFF
        $mockPdo->shouldReceive('query')
            ->once()
            ->with(Mockery::on(fn ($sql) => str_contains($sql, 'general_log')))
            ->andReturn($mockLogCheckStmt);
        $mockLogCheckStmt->shouldReceive('fetch')->once()->andReturn(['Value' => 'OFF']);

        // Mock SHOW VARIABLES for log_output
        $mockPdo->shouldReceive('query')
            ->once()
            ->with(Mockery::on(fn ($sql) => str_contains($sql, 'log_output')))
            ->andReturn($mockLogOutputStmt);
        $mockLogOutputStmt->shouldReceive('fetch')->once()->andReturn(['Value' => 'FILE']);

        // Should try performance_schema
        $mockPdo->shouldReceive('prepare')
            ->once()
            ->with(Mockery::on(fn ($sql) => str_contains($sql, 'performance_schema')))
            ->andThrow(new Exception('Not available'));

        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('connection');
        $property->setAccessible(true);
        $property->setValue($driver, $mockPdo);

        Log::shouldReceive('info')->twice();
        Log::shouldReceive('debug')->once();

        // Act
        $logs = $driver->retrieveUserQueryLogs(
            'test_user',
            Carbon::parse('2025-01-01 09:00:00'),
            Carbon::parse('2025-01-01 11:00:00')
        );

        // Assert
        $this->assertEmpty($logs);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_retrieves_logs_from_performance_schema(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $mockPdo = Mockery::mock(PDO::class);
        $mockLogCheckStmt = Mockery::mock(PDOStatement::class);
        $mockLogOutputStmt = Mockery::mock(PDOStatement::class);
        $mockPerfStmt = Mockery::mock(PDOStatement::class);

        // Mock SHOW VARIABLES for general_log - log is OFF
        $mockPdo->shouldReceive('query')
            ->once()
            ->with(Mockery::on(fn ($sql) => str_contains($sql, 'general_log')))
            ->andReturn($mockLogCheckStmt);
        $mockLogCheckStmt->shouldReceive('fetch')->once()->andReturn(['Value' => 'OFF']);

        // Mock SHOW VARIABLES for log_output
        $mockPdo->shouldReceive('query')
            ->once()
            ->with(Mockery::on(fn ($sql) => str_contains($sql, 'log_output')))
            ->andReturn($mockLogOutputStmt);
        $mockLogOutputStmt->shouldReceive('fetch')->once()->andReturn(['Value' => 'FILE']);

        $perfLogs = [
            ['query_text' => 'SELECT * FROM orders'],
            ['query_text' => 'UPDATE users SET status = 1'],
        ];

        // Mock performance_schema query
        $mockPdo->shouldReceive('prepare')
            ->once()
            ->with(Mockery::on(fn ($sql) => str_contains($sql, 'performance_schema')))
            ->andReturn($mockPerfStmt);
        $mockPerfStmt->shouldReceive('execute')->once()->andReturn(true);
        $mockPerfStmt->shouldReceive('fetchAll')->once()->andReturn($perfLogs);

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
        $this->assertCount(2, $logs);
        $this->assertEquals('SELECT * FROM orders', $logs[0]['query_text']);
        $this->assertEquals('UPDATE users SET status = 1', $logs[1]['query_text']);
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
