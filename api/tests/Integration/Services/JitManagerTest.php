<?php

namespace Tests\Integration\Services;

use App\Enums\AssetAccountType;
use App\Enums\DatabaseScope;
use App\Enums\Dbms;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Org;
use App\Models\Request;
use App\Models\Session;
use App\Models\User;
use App\Services\Jit\Databases\Contracts\DatabaseDriverInterface;
use App\Services\Jit\Databases\DatabaseDriverFactory;
use App\Services\Jit\JitManager;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class JitManagerTest extends TestCase
{
    use RefreshDatabase;

    protected JitManager $jitManager;

    protected User $user;

    protected Org $org;

    protected Asset $asset;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jitManager = $this->app->make(JitManager::class);

        // Create test data
        $this->org = Org::factory()->create();
        $this->user = User::factory()->create();
        $this->org->users()->attach($this->user->id);
        $this->asset = Asset::factory()->create([
            'org_id' => $this->org->id,
            'name' => 'test-db',
            'host' => 'localhost',
            'port' => 5432,
            'dbms' => Dbms::POSTGRESQL,
        ]);

        Auth::shouldReceive('id')->andReturn($this->user->id);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_retrieves_admin_credentials_successfully(): void
    {
        // Arrange
        $password = 'admin-secret-password';
        $adminAccount = AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
            'type' => AssetAccountType::ADMIN,
            'username' => 'admin_user',
            'password' => $password,
            'is_active' => true,
        ]);

        // Act
        $credentials = $this->jitManager->getAdminCredentials($this->asset);

        // Assert
        $this->assertIsArray($credentials);
        $this->assertArrayHasKey('username', $credentials);
        $this->assertArrayHasKey('password', $credentials);
        $this->assertEquals('admin_user', $credentials['username']);
        $this->assertEquals($password, $credentials['password']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_when_no_admin_account_exists(): void
    {
        // Arrange - no admin account created

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("No active admin account found for asset: {$this->asset->name}");

        // Act
        $this->jitManager->getAdminCredentials($this->asset);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_when_admin_account_is_inactive(): void
    {
        // Arrange
        AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
            'type' => AssetAccountType::ADMIN,
            'username' => 'admin_user',
            'is_active' => false, // Inactive
        ]);

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("No active admin account found for asset: {$this->asset->name}");

        // Act
        $this->jitManager->getAdminCredentials($this->asset);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_throws_exception_when_only_jit_account_exists(): void
    {
        // Arrange - Only JIT account, no admin
        AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
            'type' => AssetAccountType::JIT,
            'username' => 'jit_user',
            'is_active' => true,
        ]);

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("No active admin account found for asset: {$this->asset->name}");

        // Act
        $this->jitManager->getAdminCredentials($this->asset);
    }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_generates_credentials_using_database_driver(): void
    // {
    //     // Arrange
    //     $mockDriver = Mockery::mock(DatabaseDriverInterface::class);
    //     $expectedCreds = [
    //         'username' => 'argus001_abc12',
    //         'password' => 'SecureP@ssw0rd123',
    //     ];

    //     $mockDriver->shouldReceive('generateSecureCredentials')
    //         ->once()
    //         ->andReturn($expectedCreds);

    //     // Mock the static DatabaseDriverFactory::create() method
    //     $mockFactory = Mockery::mock('alias:' . DatabaseDriverFactory::class);
    //     $mockFactory->shouldReceive('create')
    //         ->once()
    //         ->with($this->asset, Mockery::type('array'), Mockery::type('array'))
    //         ->andReturn($mockDriver);

    //     // Act
    //     $credentials = $this->jitManager->generateCredentials($this->asset);

    //     // Assert
    //     $this->assertEquals($expectedCreds, $credentials);
    //     $this->assertArrayHasKey('username', $credentials);
    //     $this->assertArrayHasKey('password', $credentials);
    // }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_gets_database_driver_with_valid_credentials(): void
    // {
    //     // Arrange
    //     AssetAccount::factory()->create([
    //         'asset_id' => $this->asset->id,
    //         'type' => AssetAccountType::ADMIN,
    //         'username' => 'admin',
    //         'password' => 'admin-pass',
    //         'is_active' => true,
    //     ]);

    //     $mockDriver = Mockery::mock(DatabaseDriverInterface::class);
    //     $mockDriver->shouldReceive('testAdminConnection')
    //         ->once()
    //         ->with(Mockery::type('array'))
    //         ->andReturn(true);

    //     $mockFactory = Mockery::mock('alias:' . DatabaseDriverFactory::class);
    //     $mockFactory->shouldReceive('create')
    //         ->once()
    //         ->andReturn($mockDriver);

    //     // Act
    //     $driver = $this->jitManager->getDatabaseDriver($this->asset);

    //     // Assert
    //     $this->assertInstanceOf(DatabaseDriverInterface::class, $driver);
    // }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_throws_exception_when_database_connection_fails(): void
    // {
    //     // Arrange
    //     AssetAccount::factory()->create([
    //         'asset_id' => $this->asset->id,
    //         'type' => AssetAccountType::ADMIN,
    //         'username' => 'admin',
    //         'password' => 'admin-pass',
    //         'is_active' => true,
    //     ]);

    //     $mockDriver = Mockery::mock(DatabaseDriverInterface::class);
    //     $mockDriver->shouldReceive('testAdminConnection')
    //         ->once()
    //         ->andReturn(false); // Connection fails

    //     $mockFactory = Mockery::mock('alias:' . DatabaseDriverFactory::class);
    //     $mockFactory->shouldReceive('create')
    //         ->once()
    //         ->andReturn($mockDriver);

    //     // Assert
    //     $this->expectException(Exception::class);
    //     $this->expectExceptionMessage('Failed to connect to database with admin credentials');

    //     // Act
    //     $this->jitManager->getDatabaseDriver($this->asset);
    // }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_validates_scope_successfully(): void
    // {
    //     // Arrange
    //     AssetAccount::factory()->create([
    //         'asset_id' => $this->asset->id,
    //         'type' => AssetAccountType::ADMIN,
    //         'username' => 'admin',
    //         'password' => 'admin-pass',
    //         'is_active' => true,
    //     ]);

    //     $mockDriver = Mockery::mock(DatabaseDriverInterface::class);
    //     $mockDriver->shouldReceive('testAdminConnection')->once()->andReturn(true);
    //     $mockDriver->shouldReceive('validateScope')
    //         ->once()
    //         ->with('read')
    //         ->andReturn(true);

    //     $mockFactory = Mockery::mock('alias:' . DatabaseDriverFactory::class);
    //     $mockFactory->shouldReceive('create')->once()->andReturn($mockDriver);

    //     // Act
    //     $result = $this->jitManager->validateScope($this->asset, 'read');

    //     // Assert
    //     $this->assertTrue($result);
    // }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_returns_false_when_scope_validation_fails(): void
    // {
    //     // Arrange
    //     AssetAccount::factory()->create([
    //         'asset_id' => $this->asset->id,
    //         'type' => AssetAccountType::ADMIN,
    //         'username' => 'admin',
    //         'password' => 'admin-pass',
    //         'is_active' => true,
    //     ]);

    //     $mockDriver = Mockery::mock(DatabaseDriverInterface::class);
    //     $mockDriver->shouldReceive('testAdminConnection')->once()->andReturn(true);
    //     $mockDriver->shouldReceive('validateScope')
    //         ->once()
    //         ->with('invalid_scope')
    //         ->andReturn(false);

    //     $mockFactory = Mockery::mock('alias:' . DatabaseDriverFactory::class);
    //     $mockFactory->shouldReceive('create')->once()->andReturn($mockDriver);

    //     // Act
    //     $result = $this->jitManager->validateScope($this->asset, 'invalid_scope');

    //     // Assert
    //     $this->assertFalse($result);
    // }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_returns_false_when_scope_validation_throws_exception(): void
    // {
    //     // Arrange
    //     AssetAccount::factory()->create([
    //         'asset_id' => $this->asset->id,
    //         'type' => AssetAccountType::ADMIN,
    //         'username' => 'admin',
    //         'password' => 'admin-pass',
    //         'is_active' => true,
    //     ]);

    //     // When DatabaseDriverFactory::create throws an exception (line 293->233),
    //     // the exception is caught at line 295 and logs an error, then returns false
    //     $mockFactory = Mockery::mock('alias:' . DatabaseDriverFactory::class);
    //     $mockFactory->shouldReceive('create')
    //         ->once()
    //         ->andThrow(new Exception('Connection error'));

    //     Log::shouldReceive('error')->once();

    //     // Act
    //     $result = $this->jitManager->validateScope($this->asset, 'read');

    //     // Assert
    //     $this->assertFalse($result);
    // }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_retrieves_query_logs_for_jit_account(): void
    // {
    //     // This test is commented out because retrieveQueryLogs method doesn't exist in JitManager
    //     // Query log retrieval is now handled internally within terminateAccount method
    // }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_retrieves_query_logs_without_driver_parameter(): void
    // {
    //     // Arrange
    //     $startTime = now()->subHour();
    //     $endTime = now();

    //     $request = Request::factory()->create([
    //         'org_id' => $this->org->id,
    //         'requester_id' => $this->user->id,
    //         'asset_id' => $this->asset->id,
    //         'scope' => DatabaseScope::READ_ONLY,
    //     ]);

    //     $session = Session::factory()->create([
    //         'org_id' => $this->org->id,
    //         'request_id' => $request->id,
    //         'asset_id' => $this->asset->id,
    //         'requester_id' => $this->user->id,
    //         'start_datetime' => $startTime,
    //         'end_datetime' => $endTime,
    //     ]);

    //     $jitAccount = AssetAccount::factory()->create([
    //         'asset_id' => $this->asset->id,
    //         'type' => AssetAccountType::JIT,
    //         'username' => 'jit_user',
    //         'is_active' => true,
    //     ]);

    //     $expectedLogs = [
    //         ['query_text' => 'SELECT * FROM products', 'timestamp' => now()],
    //     ];

    //     AssetAccount::factory()->create([
    //         'asset_id' => $this->asset->id,
    //         'type' => AssetAccountType::ADMIN,
    //         'username' => 'admin',
    //         'password' => 'admin-pass',
    //         'is_active' => true,
    //     ]);

    //     $mockDriver = Mockery::mock(DatabaseDriverInterface::class);
    //     $mockDriver->shouldReceive('testAdminConnection')->once()->andReturn(true);
    //     $mockDriver->shouldReceive('retrieveUserQueryLogs')
    //         ->once()
    //         ->andReturn($expectedLogs);

    //     $mockFactory = Mockery::mock('alias:' . DatabaseDriverFactory::class);
    //     $mockFactory->shouldReceive('create')->once()->andReturn($mockDriver);

    //     // Act
    //     $logs = $this->jitManager->retrieveQueryLogs($jitAccount, $session);

    //     // Assert
    //     $this->assertCount(1, $logs);
    //     $this->assertEquals($expectedLogs, $logs);
    // }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_terminates_account_returns_early_when_no_jit_account_exists(): void
    {
        // Arrange
        $request = Request::factory()->create([
            'org_id' => $this->org->id,
            'requester_id' => $this->user->id,
            'asset_id' => $this->asset->id,
        ]);

        $session = Session::factory()->create([
            'org_id' => $this->org->id,
            'request_id' => $request->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'asset_account_id' => null, // No account
        ]);

        Log::shouldReceive('info')->zeroOrMoreTimes();
        Log::shouldReceive('warning')->zeroOrMoreTimes();
        Log::shouldReceive('error')->zeroOrMoreTimes();

        // Act & Assert: should not throw and should early-return
        $this->jitManager->terminateAccount($session);
        $this->assertTrue(true); // Test passes if no exception is thrown
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_terminates_account_returns_early_when_account_is_not_jit(): void
    {
        // Arrange
        $adminAccount = AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
            'type' => AssetAccountType::ADMIN, // Not JIT
            'username' => 'admin',
            'is_active' => true,
        ]);

        $request = Request::factory()->create([
            'org_id' => $this->org->id,
            'requester_id' => $this->user->id,
            'asset_id' => $this->asset->id,
        ]);

        $session = Session::factory()->create([
            'org_id' => $this->org->id,
            'request_id' => $request->id,
            'asset_id' => $this->asset->id,
            'requester_id' => $this->user->id,
            'asset_account_id' => $adminAccount->id,
        ]);

        Log::shouldReceive('info')->zeroOrMoreTimes();
        Log::shouldReceive('warning')->zeroOrMoreTimes();
        Log::shouldReceive('error')->zeroOrMoreTimes();

        // Act & Assert: should not throw and should early-return
        $this->jitManager->terminateAccount($session);
        $this->assertTrue(true); // Test passes if no exception is thrown
    }

    // #[\PHPUnit\Framework\Attributes\Test]
    // public function it_cleans_up_expired_jit_accounts_successfully(): void
    // {
    //     // Arrange
    //     $expiredAccount = AssetAccount::factory()->create([
    //         'asset_id' => $this->asset->id,
    //         'type' => AssetAccountType::JIT,
    //         'username' => 'jit_expired',
    //         'expires_at' => now()->subHour(), // Expired
    //         'is_active' => true,
    //     ]);

    //     AssetAccount::factory()->create([
    //         'asset_id' => $this->asset->id,
    //         'type' => AssetAccountType::ADMIN,
    //         'username' => 'admin',
    //         'password' => 'admin-pass',
    //         'is_active' => true,
    //     ]);

    //     // Mock DatabaseDriverFactory to throw exception (simulating line 321's throw)
    //     $mockFactory = Mockery::mock('alias:' . DatabaseDriverFactory::class);
    //     $mockFactory->shouldReceive('create')
    //         ->once()
    //         ->andThrow(new Exception('SecretManager::cleanupExpiredAccounts'));

    //     // The exception is caught and logged, and count remains 0
    //     Log::shouldReceive('error')->once();
    //     Log::shouldReceive('info')->once();

    //     // Act
    //     $count = $this->jitManager->cleanupExpiredAccounts();

    //     // Assert
    //     $this->assertEquals(0, $count); // Failed to cleanup due to exception
    // }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_skips_inactive_accounts_during_cleanup(): void
    {
        // Arrange
        AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
            'type' => AssetAccountType::JIT,
            'username' => 'jit_inactive',
            'expires_at' => now()->subHour(),
            'is_active' => false, // Inactive - should be skipped
        ]);

        Log::shouldReceive('info')->once();

        // Act
        $count = $this->jitManager->cleanupExpiredAccounts();

        // Assert
        $this->assertEquals(0, $count);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_skips_non_expired_jit_accounts_during_cleanup(): void
    {
        // Arrange
        AssetAccount::factory()->create([
            'asset_id' => $this->asset->id,
            'type' => AssetAccountType::JIT,
            'username' => 'jit_active',
            'expires_at' => now()->addHour(), // Not expired
            'is_active' => true,
        ]);

        Log::shouldReceive('info')->once();

        // Act
        $count = $this->jitManager->cleanupExpiredAccounts();

        // Assert
        $this->assertEquals(0, $count);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_reads_configuration_on_instantiation(): void
    {
        // Arrange & Act
        $manager = $this->app->make(JitManager::class);

        // Assert - reflection to test protected property
        $reflection = new \ReflectionClass($manager);
        $property = $reflection->getProperty('config');
        $property->setAccessible(true);
        $config = $property->getValue($manager);

        $this->assertIsArray($config);
        $this->assertArrayHasKey('connection', $config); // The config should have 'connection' key
    }
}
