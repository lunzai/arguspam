<?php

namespace Tests\Unit\Services;

use App\Services\Database\Drivers\AbstractDatabaseDriver;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Mockery;
use PDO;
use RuntimeException;
use Tests\TestCase;

class AbstractDatabaseDriverTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Create a concrete implementation for testing abstract class
     */
    protected function createDriver(array $config = []): AbstractDatabaseDriver
    {
        $defaultConfig = [
            'jit' => [
                'prefix' => 'test',
                'num_length' => 3,
                'random_length' => 5,
                'username_format' => '{prefix}{number}_{random}',
                'password_length' => 16,
                'password_letters' => true,
                'password_numbers' => true,
                'password_symbols' => true,
                'password_spaces' => false,
            ],
            'host' => 'localhost',
        ];

        $mergedConfig = array_merge($defaultConfig, $config);

        return new class($mergedConfig) extends AbstractDatabaseDriver
        {
            public $connectCalled = false;

            public $lastCredentials = null;

            protected function connect(array $credentials): void
            {
                $this->connectCalled = true;
                $this->lastCredentials = $credentials;

                // Simulate successful connection
                $this->connection = Mockery::mock(PDO::class);
            }

            protected function getDsn(array $credentials): string
            {
                return 'test:host=localhost;dbname=test';
            }

            public function createUser(string $username, string $password, string $database, string $scope, $expiresAt): bool
            {
                return true;
            }

            public function terminateUser(string $username, string $database): bool
            {
                return true;
            }

            public function retrieveUserQueryLogs(string $username, $startTime, $endTime): array
            {
                return [];
            }
        };
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_constructs_with_config(): void
    {
        // Arrange & Act
        $config = ['jit' => ['prefix' => 'argus']];
        $driver = $this->createDriver($config);

        // Assert
        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);
        $driverConfig = $property->getValue($driver);

        $this->assertArrayHasKey('jit', $driverConfig);
        $this->assertEquals('argus', $driverConfig['jit']['prefix']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_secure_credentials(): void
    {
        // Arrange
        $driver = $this->createDriver();

        // Act
        $credentials = $driver->generateSecureCredentials();

        // Assert
        $this->assertIsArray($credentials);
        $this->assertArrayHasKey('username', $credentials);
        $this->assertArrayHasKey('password', $credentials);
        $this->assertNotEmpty($credentials['username']);
        $this->assertNotEmpty($credentials['password']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_username_with_correct_format(): void
    {
        // Arrange
        $driver = $this->createDriver([
            'jit' => [
                'prefix' => 'argus',
                'num_length' => 3,
                'random_length' => 5,
                'username_format' => '{prefix}{number}_{random}',
            ],
        ]);

        // Act
        $username = $driver->generateUsername();

        // Assert
        $this->assertIsString($username);
        $this->assertStringStartsWith('argus', $username);
        // Format: argus<3-digits>_<5-random-chars>
        // Example: argus123_abc45
        $this->assertMatchesRegularExpression('/^argus\d{3}_[a-z0-9]{5}$/', $username);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_username_with_custom_format(): void
    {
        // Arrange
        $driver = $this->createDriver([
            'jit' => [
                'prefix' => 'jit',
                'num_length' => 2,
                'random_length' => 3,
                'username_format' => '{random}_{prefix}{number}',
            ],
        ]);

        // Act
        $username = $driver->generateUsername();

        // Assert
        $this->assertIsString($username);
        // Format: <3-random>_jit<2-digits>
        // Example: abc_jit12
        $this->assertMatchesRegularExpression('/^[a-z0-9]{3}_jit\d{2}$/', $username);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_username_with_default_values(): void
    {
        // Arrange
        $driver = $this->createDriver(['jit' => []]); // Empty jit config

        // Act
        $username = $driver->generateUsername();

        // Assert
        $this->assertIsString($username);
        // Should use defaults: prefix=argus, num_length=3, random_length=5
        $this->assertStringStartsWith('argus', $username);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_password_with_correct_length(): void
    {
        // Arrange
        $driver = $this->createDriver([
            'jit' => [
                'password_length' => 24,
            ],
        ]);

        // Act
        $password = $driver->generatePassword();

        // Assert
        $this->assertIsString($password);
        $this->assertEquals(24, strlen($password));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_password_with_default_length(): void
    {
        // Arrange
        $driver = $this->createDriver(['jit' => []]);

        // Act
        $password = $driver->generatePassword();

        // Assert
        $this->assertIsString($password);
        $this->assertEquals(16, strlen($password)); // Default length
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_password_with_letters_numbers_symbols(): void
    {
        // Arrange
        $driver = $this->createDriver([
            'jit' => [
                'password_length' => 32,
                'password_letters' => true,
                'password_numbers' => true,
                'password_symbols' => true,
                'password_spaces' => false,
            ],
        ]);

        // Act
        $password = $driver->generatePassword();

        // Assert
        $this->assertIsString($password);
        $this->assertEquals(32, strlen($password));
        // Since Str::password is used, we trust it generates appropriate passwords
        // We just verify it returns a string of correct length
        $this->assertDoesNotMatchRegularExpression('/\s/', $password); // No spaces
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_validates_supported_scopes(): void
    {
        // Arrange
        $driver = $this->createDriver();

        // Act & Assert
        $this->assertTrue($driver->validateScope('read'));
        $this->assertTrue($driver->validateScope('write'));
        $this->assertTrue($driver->validateScope('admin'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_rejects_unsupported_scopes(): void
    {
        // Arrange
        $driver = $this->createDriver();

        // Act & Assert
        $this->assertFalse($driver->validateScope('delete'));
        $this->assertFalse($driver->validateScope('drop'));
        $this->assertFalse($driver->validateScope('invalid'));
        $this->assertFalse($driver->validateScope(''));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_tests_admin_connection_successfully(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $credentials = [
            'host' => 'localhost',
            'username' => 'admin',
            'password' => 'pass',
        ];

        Log::shouldReceive('error')->never();

        // Act
        $result = $driver->testAdminConnection($credentials);

        // Assert
        $this->assertTrue($result);
        $this->assertTrue($driver->connectCalled);
        $this->assertEquals($credentials, $driver->lastCredentials);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_returns_false_when_connection_fails(): void
    {
        // Arrange
        $config = [
            'jit' => ['prefix' => 'test'],
            'host' => 'localhost',
        ];

        $driver = new class($config) extends AbstractDatabaseDriver
        {
            protected function connect(array $credentials): void
            {
                throw new Exception('Connection refused');
            }

            protected function getDsn(array $credentials): string
            {
                return 'test:host=localhost';
            }

            public function createUser(string $username, string $password, string $database, string $scope, $expiresAt): bool
            {
                return true;
            }

            public function terminateUser(string $username, string $database): bool
            {
                return true;
            }

            public function retrieveUserQueryLogs(string $username, $startTime, $endTime): array
            {
                return [];
            }
        };

        $credentials = ['host' => 'localhost', 'username' => 'admin', 'password' => 'pass'];

        Log::shouldReceive('error')->once()->with(
            'Failed to test admin connection',
            Mockery::type('array')
        );

        // Act
        $result = $driver->testAdminConnection($credentials);

        // Assert
        $this->assertFalse($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_logs_operations_with_context(): void
    {
        // Arrange
        $driver = $this->createDriver(['host' => '192.168.1.100']);

        Log::shouldReceive('info')->once()->with(
            'Database operation: CREATE USER',
            Mockery::on(function ($context) {
                return isset($context['driver'])
                    && isset($context['host'])
                    && $context['host'] === '192.168.1.100'
                    && isset($context['username'])
                    && $context['username'] === 'test_user';
            })
        );

        // Act
        $reflection = new \ReflectionMethod($driver, 'logOperation');
        $reflection->setAccessible(true);
        $reflection->invoke($driver, 'CREATE USER', ['username' => 'test_user']);

        // Assert - Log facade expectation is checked by Mockery
        $this->assertTrue(true);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_errors_by_logging_and_throwing_exception(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $exception = new Exception('Database error occurred');

        Log::shouldReceive('error')->once()->with(
            'Database operation failed: DROP USER',
            Mockery::type('array')
        );

        // Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Database operation failed: DROP USER');

        // Act
        $reflection = new \ReflectionMethod($driver, 'handleError');
        $reflection->setAccessible(true);
        $reflection->invoke($driver, 'DROP USER', $exception, ['username' => 'test']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_wraps_original_exception_in_runtime_exception(): void
    {
        // Arrange
        $driver = $this->createDriver();
        $originalException = new Exception('Original error');

        Log::shouldReceive('error')->once();

        try {
            // Act
            $reflection = new \ReflectionMethod($driver, 'handleError');
            $reflection->setAccessible(true);
            $reflection->invoke($driver, 'TEST', $originalException);

            $this->fail('Expected RuntimeException was not thrown');
        } catch (RuntimeException $e) {
            // Assert
            $this->assertSame($originalException, $e->getPrevious());
            $this->assertStringContainsString('Original error', $e->getMessage());
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_default_supported_scopes(): void
    {
        // Arrange
        $driver = $this->createDriver();

        // Act
        $reflection = new \ReflectionClass($driver);
        $property = $reflection->getProperty('supportedScopes');
        $property->setAccessible(true);
        $scopes = $property->getValue($driver);

        // Assert
        $this->assertEquals(['read', 'write', 'admin'], $scopes);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_unique_usernames_on_multiple_calls(): void
    {
        // Arrange
        $driver = $this->createDriver();

        // Act
        $username1 = $driver->generateUsername();
        $username2 = $driver->generateUsername();
        $username3 = $driver->generateUsername();

        // Assert
        $this->assertNotEquals($username1, $username2);
        $this->assertNotEquals($username2, $username3);
        $this->assertNotEquals($username1, $username3);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_generates_different_passwords_on_multiple_calls(): void
    {
        // Arrange
        $driver = $this->createDriver();

        // Act
        $password1 = $driver->generatePassword();
        $password2 = $driver->generatePassword();
        $password3 = $driver->generatePassword();

        // Assert
        // Passwords should be different (extremely high probability with random generation)
        $this->assertNotEquals($password1, $password2);
        $this->assertNotEquals($password2, $password3);
        $this->assertNotEquals($password1, $password3);
    }
}
