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
use App\Services\Jit\JitManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class JitManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    private function makeJitManager(?DatabaseDriverFactory $factory = null): JitManager
    {
        return new JitManager($factory ?? new DatabaseDriverFactory);
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

    public function test_get_admin_credentials_returns_array_when_admin_account_exists(): void
    {
        $asset = $this->makeAssetWithAdminAccount();
        $manager = $this->makeJitManager();

        $credentials = $manager->getAdminCredentials($asset);

        $this->assertArrayHasKey('host', $credentials);
        $this->assertArrayHasKey('port', $credentials);
        $this->assertArrayHasKey('username', $credentials);
        $this->assertArrayHasKey('password', $credentials);
    }

    public function test_get_admin_credentials_throws_when_no_admin_account(): void
    {
        $asset = Asset::factory()->create(['dbms' => Dbms::MYSQL]);
        $manager = $this->makeJitManager();

        $this->expectException(CredentialNotFoundException::class);
        $manager->getAdminCredentials($asset);
    }

    public function test_terminate_account_returns_false_when_no_asset_account(): void
    {
        $session = Session::factory()->create(['asset_account_id' => null]);
        $manager = $this->makeJitManager();

        $result = $manager->terminateAccount($session->fresh());

        $this->assertFalse($result);
    }

    public function test_terminate_account_returns_false_when_account_is_not_jit_type(): void
    {
        $adminAccount = AssetAccount::factory()->create([
            'type' => AssetAccountType::ADMIN,
        ]);
        $session = Session::factory()->create([
            'asset_id' => $adminAccount->asset_id,
            'asset_account_id' => $adminAccount->id,
        ]);
        $manager = $this->makeJitManager();

        $result = $manager->terminateAccount($session->fresh());

        $this->assertFalse($result);
    }

    public function test_terminate_account_calls_driver_and_returns_true(): void
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

        $manager = $this->makeJitManager($mockFactory);

        $result = $manager->terminateAccount($session->fresh());

        $this->assertTrue($result);
    }

    public function test_test_connection_delegates_to_driver(): void
    {
        $asset = $this->makeAssetWithAdminAccount();

        $mockDriver = $this->mock(DatabaseDriverInterface::class);
        $mockDriver->shouldReceive('testAdminConnection')->andReturn(true);
        $mockDriver->shouldReceive('testConnection')->once()->with(\Mockery::type('array'))->andReturn(true);

        $mockFactory = $this->mock(DatabaseDriverFactory::class);
        $mockFactory->shouldReceive('create')->andReturn($mockDriver);

        $manager = $this->makeJitManager($mockFactory);

        $result = $manager->testConnection($asset, ['username' => 'u', 'password' => 'p']);

        $this->assertTrue($result);
    }

    public function test_get_all_databases_returns_list_from_driver(): void
    {
        $asset = $this->makeAssetWithAdminAccount();

        $mockDriver = $this->mock(DatabaseDriverInterface::class);
        $mockDriver->shouldReceive('testAdminConnection')->andReturn(true);
        $mockDriver->shouldReceive('getAllDatabases')->once()->andReturn(['db1', 'db2']);

        $mockFactory = $this->mock(DatabaseDriverFactory::class);
        $mockFactory->shouldReceive('create')->andReturn($mockDriver);

        $manager = $this->makeJitManager($mockFactory);

        $databases = $manager->getAllDatabases($asset);

        $this->assertEquals(['db1', 'db2'], $databases);
    }
}
