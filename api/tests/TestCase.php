<?php

namespace Tests;

use App\Models\Asset;
use App\Models\AssetAccount;
use App\Models\Request;
use App\Models\Session;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';
        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // Set up test database connection for integration tests
        if (config('database.connections.testing_mysql')) {
            config(['database.default' => 'testing_mysql']);
        }
    }

    /**
     * Create a test asset with admin account
     */
    protected function createTestAsset(array $attributes = []): Asset
    {
        $asset = Asset::factory()->create(array_merge([
            'dbms' => \App\Enums\Dbms::MYSQL,
            'host' => '127.0.0.1',
            'port' => 3306,
        ], $attributes));

        AssetAccount::factory()->create([
            'asset_id' => $asset->id,
            'type' => \App\Enums\AssetAccountType::ADMIN,
            'is_active' => true,
            'username' => 'test_admin',
            'password' => 'test_password',
            'databases' => ['test_db'],
        ]);

        return $asset;
    }

    /**
     * Create a test session with request
     */
    protected function createTestSession(array $attributes = []): Session
    {
        $request = Request::factory()->create(array_merge([
            'databases' => ['test_db'],
            'scope' => \App\Enums\DatabaseScope::READ_ONLY,
        ], $attributes['request'] ?? []));

        return Session::factory()->create(array_merge([
            'request_id' => $request->id,
            'asset_id' => $request->asset_id,
            'scheduled_end_datetime' => now()->addHours(2),
        ], $attributes));
    }

    /**
     * Assert that a JIT account was created correctly
     */
    protected function assertJitAccountCreated(Session $session, ?array $expectedDatabases = null): AssetAccount
    {
        $session->refresh();
        $this->assertNotNull($session->asset_account_id);

        $jitAccount = AssetAccount::find($session->asset_account_id);
        $this->assertNotNull($jitAccount);
        $this->assertEquals(\App\Enums\AssetAccountType::JIT, $jitAccount->type);
        $this->assertTrue($jitAccount->is_active);

        if ($expectedDatabases !== null) {
            $this->assertEquals($expectedDatabases, $jitAccount->databases);
        }

        return $jitAccount;
    }

    /**
     * Assert that a JIT account was terminated correctly
     */
    protected function assertJitAccountTerminated(AssetAccount $jitAccount): void
    {
        $this->assertDatabaseMissing('asset_accounts', [
            'id' => $jitAccount->id,
        ]);
    }
}
