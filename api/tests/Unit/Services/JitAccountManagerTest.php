<?php

namespace Tests\Unit\Services;

use App\Enums\AssetAccountType;
use App\Models\AssetAccount;
use App\Models\Session;
use App\Services\Jit\Database\Contracts\DatabaseDriverInterface;
use App\Services\Jit\Database\DatabaseDriverFactory;
use App\Services\Jit\Repositories\Contracts\AssetRepositoryInterface;
use App\Services\Jit\UserCreationValidator;
use App\Services\JitAccount\JitAccountManager;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class JitAccountManagerTest extends TestCase
{
    private JitAccountManager $jitAccountManager;
    private AssetRepositoryInterface $mockRepository;
    private DatabaseDriverFactory $mockDriverFactory;
    private UserCreationValidator $mockValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->mockDriverFactory = $this->createMock(DatabaseDriverFactory::class);
        $this->mockValidator = $this->createMock(UserCreationValidator::class);

        $this->jitAccountManager = new JitAccountManager(
            $this->mockRepository,
            $this->mockValidator,
            $this->mockDriverFactory,
            config('pam.database', [])
        );
    }

    public function test_it_creates_jit_account_successfully()
    {
        // Arrange
        $session = Session::factory()->create([
            'asset_id' => 1,
            'scheduled_end_datetime' => now()->addHours(2),
        ]);

        $mockDriver = $this->createMock(DatabaseDriverInterface::class);
        $mockDriver->expects($this->once())
            ->method('generateSecureCredentials')
            ->willReturn([
                'username' => 'jit_user_123',
                'password' => 'secure_password_456',
            ]);

        $mockDriver->expects($this->once())
            ->method('createUser')
            ->willReturn(true);

        $this->mockValidator
            ->expects($this->once())
            ->method('validateUserCreation')
            ->willReturn(null); // void method

        Auth::shouldReceive('id')->andReturn(1);

        // Act
        $result = $this->jitAccountManager->createAccount($session, $mockDriver, ['test_db']);

        // Assert
        $this->assertInstanceOf(\App\Models\AssetAccount::class, $result);
        $this->assertEquals('jit_user_123', $result->username);

        // Verify account was created in database
        $this->assertDatabaseHas('asset_accounts', [
            'asset_id' => $session->asset_id,
            'username' => 'jit_user_123',
            'type' => AssetAccountType::JIT,
            'is_active' => true,
            'databases' => json_encode(['test_db']),
        ]);

        // Verify session was updated
        $session->refresh();
        $this->assertNotNull($session->asset_account_id);
        $this->assertEquals('jit_user_123', $session->account_name);
    }

    public function test_it_handles_validation_failure()
    {
        // Arrange
        $session = Session::factory()->create();
        $mockDriver = $this->createMock(DatabaseDriverInterface::class);
        $mockDriver->expects($this->once())
            ->method('generateSecureCredentials')
            ->willReturn([
                'username' => 'invalid_user',
                'password' => 'weak',
            ]);

        $this->mockValidator
            ->expects($this->once())
            ->method('validateUserCreation')
            ->willThrowException(new \App\Exceptions\ValidationException(
                'Validation failed',
                ['Username too short', 'Password too weak']
            ));

        // Act & Assert
        $this->expectException(\App\Exceptions\JitAccountException::class);
        $this->expectExceptionMessage('User creation validation failed: Validation failed');

        $this->jitAccountManager->createAccount($session, $mockDriver, ['test_db']);
    }

    public function test_it_handles_database_user_creation_failure()
    {
        // Arrange
        $session = Session::factory()->create();
        $mockDriver = $this->createMock(DatabaseDriverInterface::class);
        $mockDriver->expects($this->once())
            ->method('generateSecureCredentials')
            ->willReturn([
                'username' => 'jit_user_123',
                'password' => 'secure_password_456',
            ]);

        $mockDriver->expects($this->once())
            ->method('createUser')
            ->willReturn(false);

        $this->mockValidator
            ->expects($this->once())
            ->method('validateUserCreation')
            ->willReturn(null); // void method

        // Act & Assert
        $this->expectException(\App\Exceptions\JitAccountException::class);
        $this->expectExceptionMessage('Failed to create JIT user in database');

        $this->jitAccountManager->createAccount($session, $mockDriver, ['test_db']);
    }

    public function test_it_terminates_jit_account_successfully()
    {
        // Arrange
        $session = Session::factory()->create();
        $jitAccount = AssetAccount::factory()->create([
            'type' => AssetAccountType::JIT,
            'username' => 'jit_user_123',
        ]);

        $mockDriver = $this->createMock(DatabaseDriverInterface::class);
        $mockDriver->expects($this->once())
            ->method('terminateUser')
            ->with('jit_user_123', 'test_db')
            ->willReturn(true);

        $adminCredentials = [
            'databases' => ['test_db'],
        ];

        // Act
        $this->jitAccountManager->terminateAccount($session, $mockDriver, $jitAccount, $adminCredentials);

        // Assert - no exception thrown means success

        // Verify account was deleted
        $this->assertDatabaseMissing('asset_accounts', [
            'id' => $jitAccount->id,
        ]);
    }

    public function test_it_handles_termination_without_database_info()
    {
        // Arrange
        $session = Session::factory()->create();
        $jitAccount = AssetAccount::factory()->create([
            'type' => AssetAccountType::JIT,
            'username' => 'jit_user_123',
        ]);

        $mockDriver = $this->createMock(DatabaseDriverInterface::class);
        $mockDriver->expects($this->never())
            ->method('terminateUser');

        $adminCredentials = []; // No database info

        // Act & Assert
        $this->expectException(\App\Exceptions\JitAccountException::class);
        $this->expectExceptionMessage('Cannot determine database for termination');

        $this->jitAccountManager->terminateAccount($session, $mockDriver, $jitAccount, $adminCredentials);
    }
}
