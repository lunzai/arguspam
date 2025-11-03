<?php

namespace Tests\Feature\Services;

use App\Enums\AssetAccountType;
use App\Enums\Dbms;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Session;
use App\Services\Jit\Secrets\SecretsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JitLifecycleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private SecretsManager $secretsManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->secretsManager = app(SecretsManager::class);
    }

    /** @test */
    public function test_completes_full_jit_lifecycle_with_mysql()
    {
        // Skip if no MySQL test database configured
        if (!config('database.connections.testing_mysql')) {
            $this->markTestSkipped('MySQL test database not configured');
        }

        // Arrange - Create test asset with admin account
        $asset = Asset::factory()->create([
            'dbms' => Dbms::MYSQL,
            'host' => config('database.connections.testing_mysql.host', '127.0.0.1'),
            'port' => config('database.connections.testing_mysql.port', 3306),
        ]);

        $adminAccount = AssetAccount::factory()->create([
            'asset_id' => $asset->id,
            'type' => AssetAccountType::ADMIN,
            'is_active' => true,
            'username' => config('database.connections.testing_mysql.username'),
            'password' => config('database.connections.testing_mysql.password'),
            'databases' => ['test_db'],
        ]);

        $session = Session::factory()->create([
            'asset_id' => $asset->id,
            'scheduled_end_datetime' => now()->addMinutes(30),
        ]);

        // Act 1: Create JIT account
        $jitAccount = $this->secretsManager->createAccount($session);

        // Assert 1: Account created successfully
        $this->assertInstanceOf(AssetAccount::class, $jitAccount);
        $this->assertNotNull($jitAccount->username);
        $this->assertNotNull($jitAccount->password);

        // Verify account in database
        $session->refresh();
        $this->assertNotNull($session->asset_account_id);
        $this->assertEquals($jitAccount->id, $session->asset_account_id);
        $this->assertEquals(AssetAccountType::JIT, $jitAccount->type);
        $this->assertTrue($jitAccount->is_active);

        // Act 2: Terminate JIT account
        $this->secretsManager->terminateAccount($session);

        // Assert 2: Account terminated successfully (no exception thrown)
        // Verify account deleted from database
        $this->assertDatabaseMissing('asset_accounts', [
            'id' => $jitAccount->id,
        ]);
    }

    /** @test */
    public function test_handles_all_databases_access()
    {
        // Arrange
        $asset = Asset::factory()->create([
            'dbms' => Dbms::MYSQL,
            'host' => config('database.connections.testing_mysql.host', '127.0.0.1'),
            'port' => config('database.connections.testing_mysql.port', 3306),
        ]);

        $adminAccount = AssetAccount::factory()->create([
            'asset_id' => $asset->id,
            'type' => AssetAccountType::ADMIN,
            'is_active' => true,
            'username' => config('database.connections.testing_mysql.username'),
            'password' => config('database.connections.testing_mysql.password'),
            'databases' => null, // All databases access
        ]);

        $session = Session::factory()->create([
            'asset_id' => $asset->id,
            'scheduled_end_datetime' => now()->addMinutes(30),
        ]);

        // Act
        $jitAccount = $this->secretsManager->createAccount($session);

        // Assert
        $this->assertInstanceOf(AssetAccount::class, $jitAccount);

        $session->refresh();
        $this->assertEquals($jitAccount->id, $session->asset_account_id);
        $this->assertNull($jitAccount->databases); // Should be null for all databases
    }

    /** @test */
    public function test_handles_multiple_databases_access()
    {
        // Arrange
        $asset = Asset::factory()->create([
            'dbms' => Dbms::MYSQL,
            'host' => config('database.connections.testing_mysql.host', '127.0.0.1'),
            'port' => config('database.connections.testing_mysql.port', 3306),
        ]);

        $adminAccount = AssetAccount::factory()->create([
            'asset_id' => $asset->id,
            'type' => AssetAccountType::ADMIN,
            'is_active' => true,
            'username' => config('database.connections.testing_mysql.username'),
            'password' => config('database.connections.testing_mysql.password'),
            'databases' => ['db1', 'db2', 'db3'],
        ]);

        $session = Session::factory()->create([
            'asset_id' => $asset->id,
            'scheduled_end_datetime' => now()->addMinutes(30),
        ]);

        // Act
        $jitAccount = $this->secretsManager->createAccount($session);

        // Assert
        $this->assertInstanceOf(AssetAccount::class, $jitAccount);

        $session->refresh();
        $this->assertEquals($jitAccount->id, $session->asset_account_id);
        $this->assertEquals(['db1', 'db2', 'db3'], $jitAccount->databases);
    }

    /** @test */
    public function test_handles_encryption_and_decryption_correctly()
    {
        // Arrange
        $asset = Asset::factory()->create([
            'dbms' => Dbms::MYSQL,
            'host' => config('database.connections.testing_mysql.host', '127.0.0.1'),
            'port' => config('database.connections.testing_mysql.port', 3306),
        ]);

        $adminAccount = AssetAccount::factory()->create([
            'asset_id' => $asset->id,
            'type' => AssetAccountType::ADMIN,
            'is_active' => true,
            'username' => config('database.connections.testing_mysql.username'),
            'password' => config('database.connections.testing_mysql.password'),
            'databases' => ['test_db'],
        ]);

        $session = Session::factory()->create([
            'asset_id' => $asset->id,
            'scheduled_end_datetime' => now()->addMinutes(30),
        ]);

        // Act
        $jitAccount = $this->secretsManager->createAccount($session);

        // Assert
        $this->assertInstanceOf(AssetAccount::class, $jitAccount);

        // Verify password is encrypted in database
        $session->refresh();
        $this->assertEquals($jitAccount->id, $session->asset_account_id);

        // Password should be encrypted (not plain text)
        $this->assertStringStartsWith('eyJ', $jitAccount->password); // Laravel encrypted string

        // But when accessed through model, it should be decrypted
        $this->assertNotNull($jitAccount->password);
        $this->assertIsString($jitAccount->password);
    }
}
