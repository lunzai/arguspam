<?php

namespace Tests\Feature\Services;

use App\Enums\AssetAccountType;
use App\Enums\Dbms;
use App\Models\AssetAccount;
use App\Services\Jit\Secrets\SecretsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecretsManagerTest extends TestCase
{
    use RefreshDatabase;

    private SecretsManager $secretsManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->secretsManager = app(SecretsManager::class);
    }

    /** @test */
    public function it_creates_account_with_admin_credentials_databases()
    {
        // Arrange
        $asset = $this->createTestAsset([
            'dbms' => Dbms::MYSQL,
        ]);

        // Update admin account to have specific databases
        $adminAccount = AssetAccount::where('asset_id', $asset->id)
            ->where('type', AssetAccountType::ADMIN)
            ->first();
        $adminAccount->update(['databases' => ['admin_db1', 'admin_db2']]);

        $session = $this->createTestSession([
            'asset_id' => $asset->id,
        ]);

        // Act
        $jitAccount = $this->secretsManager->createAccount($session);

        // Assert
        $this->assertInstanceOf(\App\Models\AssetAccount::class, $jitAccount);
        $this->assertJitAccountCreated($session, ['admin_db1', 'admin_db2']);
    }

    /** @test */
    public function it_creates_account_with_request_databases()
    {
        // Arrange
        $asset = $this->createTestAsset();
        $adminAccount = AssetAccount::where('asset_id', $asset->id)
            ->where('type', AssetAccountType::ADMIN)
            ->first();
        $adminAccount->update(['databases' => null]); // No admin databases

        $session = $this->createTestSession([
            'asset_id' => $asset->id,
            'request' => ['databases' => ['request_db1', 'request_db2']],
        ]);

        // Act
        $jitAccount = $this->secretsManager->createAccount($session);

        // Assert
        $this->assertInstanceOf(\App\Models\AssetAccount::class, $jitAccount);
        $this->assertJitAccountCreated($session, ['request_db1', 'request_db2']);
    }

    /** @test */
    public function it_creates_account_with_asset_databases()
    {
        // Arrange
        $asset = $this->createTestAsset([
            'databases' => ['asset_db1', 'asset_db2'],
        ]);

        $adminAccount = AssetAccount::where('asset_id', $asset->id)
            ->where('type', AssetAccountType::ADMIN)
            ->first();
        $adminAccount->update(['databases' => null]); // No admin databases

        $session = $this->createTestSession([
            'asset_id' => $asset->id,
            'request' => ['databases' => null], // No request databases
        ]);

        // Act
        $jitAccount = $this->secretsManager->createAccount($session);

        // Assert
        $this->assertInstanceOf(\App\Models\AssetAccount::class, $jitAccount);
        $this->assertJitAccountCreated($session, ['asset_db1', 'asset_db2']);
    }

    /** @test */
    public function it_creates_account_with_all_databases_access()
    {
        // Arrange
        $asset = $this->createTestAsset();
        $adminAccount = AssetAccount::where('asset_id', $asset->id)
            ->where('type', AssetAccountType::ADMIN)
            ->first();
        $adminAccount->update(['databases' => null]); // All databases

        $session = $this->createTestSession([
            'asset_id' => $asset->id,
            'request' => ['databases' => null], // No specific databases
        ]);

        // Act
        $jitAccount = $this->secretsManager->createAccount($session);

        // Assert
        $this->assertInstanceOf(\App\Models\AssetAccount::class, $jitAccount);
        $this->assertJitAccountCreated($session, null); // null = all databases
    }

    /** @test */
    public function it_handles_missing_admin_account()
    {
        // Arrange
        $asset = $this->createTestAsset();

        // Delete admin account
        AssetAccount::where('asset_id', $asset->id)
            ->where('type', AssetAccountType::ADMIN)
            ->delete();

        $session = $this->createTestSession(['asset_id' => $asset->id]);

        // Act & Assert
        $this->expectException(\App\Exceptions\JitAccountException::class);
        $this->expectExceptionMessage('Failed to create JIT account: No active admin account found for asset:');

        $this->secretsManager->createAccount($session);
    }

    /** @test */
    public function it_terminates_account_successfully()
    {
        // Arrange
        $asset = $this->createTestAsset();
        $session = $this->createTestSession(['asset_id' => $asset->id]);

        // Create JIT account first
        $jitAccount = $this->secretsManager->createAccount($session);
        $this->assertInstanceOf(\App\Models\AssetAccount::class, $jitAccount);

        $session->refresh();

        // Act
        $this->secretsManager->terminateAccount($session);

        // Assert - no exception thrown means success
        $this->assertJitAccountTerminated($jitAccount);
    }

    /** @test */
    public function it_handles_termination_without_jit_account()
    {
        // Arrange
        $session = $this->createTestSession();

        // Act
        $this->secretsManager->terminateAccount($session);

        // Assert - no exception thrown means success (no JIT account found, which is expected)
    }
}
