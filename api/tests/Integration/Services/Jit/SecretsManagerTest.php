<?php

namespace Tests\Integration\Services\Jit;

use App\Enums\AssetAccountType;
use App\Enums\Dbms;
use App\Exceptions\CredentialNotFoundException;
use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Session;
use App\Services\Jit\Databases\Contracts\DatabaseDriverInterface;
use App\Services\Jit\Databases\DatabaseDriverFactory;
use App\Services\Jit\Secrets\SecretsManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class SecretsManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    private function makeSecretsManager(?DatabaseDriverFactory $factory = null): SecretsManager
    {
        return new SecretsManager($factory ?? new DatabaseDriverFactory);
    }

    private function makeAssetWithAdminAccount(): Asset
    {
        $asset = Asset::factory()->create(['dbms' => Dbms::MYSQL]);
        AssetAccount::factory()->create([
            'asset_id' => $asset->id,
            'type' => AssetAccountType::ADMIN,
            'is_active' => true,
            'databases' => ['testdb'],
        ]);
        return $asset->fresh();
    }

    public function test_get_admin_credentials_returns_connection_details(): void
    {
        $asset = $this->makeAssetWithAdminAccount();
        $manager = $this->makeSecretsManager();

        $credentials = $manager->getAdminCredentials($asset);

        $this->assertArrayHasKey('host', $credentials);
        $this->assertArrayHasKey('port', $credentials);
        $this->assertArrayHasKey('username', $credentials);
        $this->assertArrayHasKey('password', $credentials);
    }

    public function test_get_admin_credentials_throws_when_no_admin_account(): void
    {
        $asset = Asset::factory()->create(['dbms' => Dbms::MYSQL]);
        $manager = $this->makeSecretsManager();

        $this->expectException(CredentialNotFoundException::class);
        $manager->getAdminCredentials($asset);
    }

    public function test_terminate_account_returns_false_when_no_asset_account(): void
    {
        $session = Session::factory()->create(['asset_account_id' => null]);
        $manager = $this->makeSecretsManager();

        $result = $manager->terminateAccount($session->fresh());

        $this->assertFalse($result);
    }

    public function test_terminate_account_returns_false_for_non_jit_account(): void
    {
        $adminAccount = AssetAccount::factory()->create([
            'type' => AssetAccountType::ADMIN,
        ]);
        $session = Session::factory()->create([
            'asset_id' => $adminAccount->asset_id,
            'asset_account_id' => $adminAccount->id,
        ]);
        $manager = $this->makeSecretsManager();

        $result = $manager->terminateAccount($session->fresh());

        $this->assertFalse($result);
    }

    public function test_terminate_account_calls_driver_terminate_and_returns_true(): void
    {
        $asset = $this->makeAssetWithAdminAccount();
        $jitAccount = AssetAccount::factory()->create([
            'asset_id' => $asset->id,
            'type' => AssetAccountType::JIT,
            'is_active' => true,
        ]);
        $session = Session::factory()->create([
            'asset_id' => $asset->id,
            'asset_account_id' => $jitAccount->id,
        ]);

        $mockDriver = $this->mock(DatabaseDriverInterface::class);
        $mockDriver->shouldReceive('testAdminConnection')->andReturn(true);
        $mockDriver->shouldReceive('retrieveUserQueryLogs')->andReturn([]);
        $mockDriver->shouldReceive('terminateUser')->once()->andReturn(true);

        $mockFactory = $this->mock(DatabaseDriverFactory::class);
        $mockFactory->shouldReceive('create')->andReturn($mockDriver);

        $manager = $this->makeSecretsManager($mockFactory);

        $result = $manager->terminateAccount($session->fresh());

        $this->assertTrue($result);
    }

    public function test_get_all_databases_returns_list_from_driver(): void
    {
        $asset = $this->makeAssetWithAdminAccount();

        $mockDriver = $this->mock(DatabaseDriverInterface::class);
        $mockDriver->shouldReceive('testAdminConnection')->andReturn(true);
        $mockDriver->shouldReceive('getAllDatabases')->once()->andReturn(['production', 'staging']);

        $mockFactory = $this->mock(DatabaseDriverFactory::class);
        $mockFactory->shouldReceive('create')->andReturn($mockDriver);

        $manager = $this->makeSecretsManager($mockFactory);

        $databases = $manager->getAllDatabases($asset);

        $this->assertEquals(['production', 'staging'], $databases);
    }
}
