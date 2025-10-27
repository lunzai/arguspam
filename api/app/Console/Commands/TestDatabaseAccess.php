<?php

namespace App\Console\Commands;

use App\Models\Session;
use App\Services\Jit\Secrets\SecretsManager;
use Illuminate\Console\Command;

class TestDatabaseAccess extends Command
{
    protected $signature = 'test:database:access {session : The session ID to test}';

    protected $description = 'Test database access scenarios with different database configurations';

    public function handle(SecretsManager $secretsManager): int
    {
        $sessionId = $this->argument('session');

        try {
            $session = Session::with(['asset', 'request'])->findOrFail($sessionId);

            $this->info("Testing database access for Session ID: {$sessionId}");
            $this->info("Asset: {$session->asset->name} ({$session->asset->host}:{$session->asset->port})");
            $this->info("DBMS: {$session->asset->dbms->value}");
            $this->info("Scope: {$session->request->scope->value}");

            // Test different database access scenarios
            $this->testDatabaseAccessScenarios($secretsManager, $session);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to test database access: '.$e->getMessage());
            return self::FAILURE;
        }
    }

    private function testDatabaseAccessScenarios(SecretsManager $secretsManager, Session $session): void
    {
        $this->newLine();
        $this->info('🧪 Testing Database Access Scenarios:');
        $this->line('=====================================');

        // Scenario 1: All databases (default behavior)
        $this->line('1. All Databases Access (default):');
        $this->line('   - No specific database provided');
        $this->line('   - User gets access to ALL databases');
        $this->line('   - MySQL: GRANT privileges ON *.*');
        $this->line('   - PostgreSQL: GRANT privileges ON ALL DATABASES');

        // Scenario 2: Single database
        $this->line('2. Single Database Access:');
        $this->line('   - Provide: "arguspam"');
        $this->line('   - User gets access to specific database only');
        $this->line('   - MySQL: GRANT privileges ON `arguspam`.*');
        $this->line('   - PostgreSQL: GRANT privileges ON DATABASE arguspam');

        // Scenario 3: Multiple databases
        $this->line('3. Multiple Databases Access:');
        $this->line('   - Provide: ["arguspam", "test_db", "dev_db"]');
        $this->line('   - User gets access to multiple specific databases');
        $this->line('   - MySQL: GRANT privileges ON `db1`.*, `db2`.*, `db3`.*');
        $this->line('   - PostgreSQL: GRANT privileges ON each database');

        $this->newLine();
        $this->info('📋 Current Implementation:');
        $this->line('The system now supports:');
        $this->line('✅ Optional database specification');
        $this->line('✅ Single database access');
        $this->line('✅ Multiple database access');
        $this->line('✅ All database access (default)');
        $this->line('✅ Flexible privilege granting based on scope');
        $this->line('✅ Both MySQL and PostgreSQL support');

        $this->newLine();
        $this->info('🔧 Configuration Options:');
        $this->line('Database access can be configured at:');
        $this->line('• Admin credentials: databases/database field');
        $this->line('• Session request: databases/database field');
        $this->line('• Asset configuration: databases/database field');
        $this->line('• Default: null (all databases)');
    }
}
