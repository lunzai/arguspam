<?php

namespace Tests\Unit\Services;

use App\Models\AssetAccount;
use App\Services\Jit\CredentialManager;
use App\Services\Jit\Database\DatabaseDriverFactory;
use App\Services\Jit\Repositories\Contracts\AssetRepositoryInterface;
use App\Services\Jit\UserCreationValidator;
use Tests\TestCase;

class CredentialManagerTest extends TestCase
{
    private CredentialManager $credentialManager;
    private AssetRepositoryInterface $mockRepository;
    private DatabaseDriverFactory $mockDriverFactory;
    private UserCreationValidator $mockValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockRepository = $this->createMock(AssetRepositoryInterface::class);
        $this->mockDriverFactory = $this->createMock(DatabaseDriverFactory::class);
        $this->mockValidator = $this->createMock(UserCreationValidator::class);

        $this->credentialManager = new CredentialManager(
            $this->mockRepository,
            $this->mockValidator,
            $this->mockDriverFactory,
            config('pam.database', [])
        );
    }

    public function test_it_retrieves_admin_credentials_successfully()
    {
        // Arrange
        $asset = $this->createMock(\App\Models\Asset::class);
        $asset->id = 1;
        $asset->name = 'Test Asset';

        // Create a real AssetAccount object for testing
        $adminAccount = new \App\Models\AssetAccount;
        $adminAccount->username = 'admin_user';
        $adminAccount->password = 'admin_password';
        $adminAccount->databases = ['test_db1', 'test_db2'];

        $this->mockRepository
            ->expects($this->once())
            ->method('getActiveAdminAccount')
            ->with($asset)
            ->willReturn($adminAccount);

        // Act
        $result = $this->credentialManager->getAdminCredentials($asset);

        // Assert
        $this->assertEquals('admin_user', $result['username']);
        $this->assertEquals('admin_password', $result['password']);
        $this->assertEquals(['test_db1', 'test_db2'], $result['databases']);
    }

    public function test_it_throws_exception_when_no_admin_account_found()
    {
        // Arrange
        $asset = $this->createMock(\App\Models\Asset::class);
        $asset->id = 1;
        $asset->name = 'Test Asset';

        $this->mockRepository
            ->expects($this->once())
            ->method('getActiveAdminAccount')
            ->with($asset)
            ->willReturn(null);

        // Act & Assert
        $this->expectException(\App\Exceptions\CredentialNotFoundException::class);
        $this->expectExceptionMessage('No active admin account found for asset: Test Asset');

        $this->credentialManager->getAdminCredentials($asset);
    }

    public function test_it_handles_exceptions_gracefully()
    {
        // Arrange
        $asset = $this->createMock(\App\Models\Asset::class);
        $asset->id = 1;
        $asset->name = 'Test Asset';

        $this->mockRepository
            ->expects($this->once())
            ->method('getActiveAdminAccount')
            ->with($asset)
            ->willThrowException(new \Exception('Database connection failed'));

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to get admin credentials: Database connection failed');

        $this->credentialManager->getAdminCredentials($asset);
    }

    public function test_it_generates_credentials_successfully()
    {
        // Arrange
        $asset = $this->createMock(\App\Models\Asset::class);
        $asset->id = 1;
        $asset->name = 'Test Asset';

        $adminAccount = $this->createMock(\App\Models\AssetAccount::class);
        $adminAccount->username = 'admin_user';
        $adminAccount->password = 'admin_password';
        $adminAccount->databases = ['test_db'];

        $this->mockRepository
            ->expects($this->once())
            ->method('getActiveAdminAccount')
            ->with($asset)
            ->willReturn($adminAccount);

        $mockDriver = $this->createMock(\App\Services\Jit\Database\Contracts\DatabaseDriverInterface::class);
        $mockDriver->expects($this->once())
            ->method('generateSecureCredentials')
            ->willReturn([
                'username' => 'jit_user_123',
                'password' => 'secure_password_456',
            ]);

        $this->mockDriverFactory
            ->expects($this->once())
            ->method('create')
            ->willReturn($mockDriver);

        $this->mockValidator
            ->expects($this->once())
            ->method('validateUserCreation')
            ->willReturn(null); // void method

        // Act
        $result = $this->credentialManager->generateCredentials($asset);

        // Assert
        $this->assertEquals('jit_user_123', $result['username']);
        $this->assertEquals('secure_password_456', $result['password']);
    }
}
