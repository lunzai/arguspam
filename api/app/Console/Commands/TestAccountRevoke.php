<?php

namespace App\Console\Commands;

use App\Models\Session;
use App\Models\SessionAudit;
use App\Services\Secrets\SecretsManager;
use Illuminate\Console\Command;

class TestAccountRevoke extends Command
{
    protected $signature = 'test:account:revoke {session : The session ID to revoke JIT account for}';

    protected $description = 'Revoke a JIT account for a session and output query history';

    public function handle(SecretsManager $secretsManager): int
    {
        $sessionId = $this->argument('session');

        try {
            // Find the session
            $session = Session::with(['asset', 'assetAccount', 'request'])->findOrFail($sessionId);
            
            $this->info("Revoking JIT account for Session ID: {$sessionId}");
            $this->info("Asset: {$session->asset->name} ({$session->asset->host}:{$session->asset->port})");
            $this->info("DBMS: {$session->asset->dbms->value}");
            
            if (!$session->assetAccount) {
                $this->error('❌ No JIT account found for this session');
                return self::FAILURE;
            }
            
            $this->info("JIT Account: {$session->assetAccount->username}");
            $this->info("Account ID: {$session->assetAccount->id}");
            
            // Show database access information
            $this->showDatabaseAccessInfo($session);
            
            // Revoke the JIT account
            $results = $secretsManager->terminateAccount($session);
            
            $this->newLine();
            $this->info('✅ JIT Account revocation completed!');
            $this->newLine();
            
            // Display results
            $this->line('📋 Revocation Results:');
            $this->line("   Database user terminated: " . ($results['terminated'] ? '✅ Yes' : '❌ No'));
            $this->line("   Audit logs retrieved: " . ($results['audit_logs_retrieved'] ? '✅ Yes' : '❌ No'));
            $this->line("   Account record deleted: " . ($results['account_deleted'] ? '✅ Yes' : '❌ No'));
            
            if (isset($results['audit_log_count'])) {
                $this->line("   Query logs found: {$results['audit_log_count']}");
            }
            
            if (!empty($results['errors'])) {
                $this->newLine();
                $this->warn('⚠️  Errors encountered:');
                foreach ($results['errors'] as $error) {
                    $this->line("   - {$error}");
                }
            }
            
            // Display query history from database
            $this->newLine();
            $this->line('📊 Query History from Database:');
            $this->line('================================');
            
            $auditLogs = SessionAudit::where('session_id', $sessionId)
                ->orderBy('query_timestamp')
                ->get();
            
            if ($auditLogs->isEmpty()) {
                $this->warn('   No query logs found in database');
            } else {
                foreach ($auditLogs as $log) {
                    $this->line("   [{$log->query_timestamp}] {$log->query_text}");
                }
            }
            
            $this->newLine();
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to revoke JIT account: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function showDatabaseAccessInfo(Session $session): void
    {
        $this->newLine();
        $this->line('🗄️  Database Access Configuration:');
        $this->line('===================================');

        // Check admin credentials
        try {
            $secretsManager = app(SecretsManager::class);
            $adminCreds = $secretsManager->getAdminCredentials($session->asset);
            
            if (isset($adminCreds['databases'])) {
                $this->line("   Admin databases: " . (is_array($adminCreds['databases']) ? implode(', ', $adminCreds['databases']) : $adminCreds['databases']));
            } elseif (isset($adminCreds['database'])) {
                $this->line("   Admin database: {$adminCreds['database']}");
            } else {
                $this->line("   Admin database: Not specified");
            }
        } catch (\Exception $e) {
            $this->line("   Admin credentials: Error retrieving");
        }

        // Check session/request database settings
        if (isset($session->request->databases)) {
            $this->line("   Request databases: " . (is_array($session->request->databases) ? implode(', ', $session->request->databases) : $session->request->databases));
        } elseif (isset($session->request->database)) {
            $this->line("   Request database: {$session->request->database}");
        } else {
            $this->line("   Request database: Not specified");
        }

        // Check asset database settings
        if (isset($session->asset->databases)) {
            $this->line("   Asset databases: " . (is_array($session->asset->databases) ? implode(', ', $session->asset->databases) : $session->asset->databases));
        } elseif (isset($session->asset->database)) {
            $this->line("   Asset database: {$session->asset->database}");
        } else {
            $this->line("   Asset database: Not specified");
        }

        // Show final access level
        $this->line("   Final access: " . $this->getFinalAccessDescription($session));
        $this->newLine();
    }

    private function getFinalAccessDescription(Session $session): string
    {
        try {
            $secretsManager = app(SecretsManager::class);
            $adminCreds = $secretsManager->getAdminCredentials($session->asset);
            
            // Check in order of precedence
            if (isset($adminCreds['databases'])) {
                return is_array($adminCreds['databases']) ? 
                    "Multiple databases: " . implode(', ', $adminCreds['databases']) : 
                    "Single database: {$adminCreds['databases']}";
            }
            
            if (isset($adminCreds['database'])) {
                return "Single database: {$adminCreds['database']}";
            }
            
            if (isset($session->request->databases)) {
                return is_array($session->request->databases) ? 
                    "Multiple databases: " . implode(', ', $session->request->databases) : 
                    "Single database: {$session->request->databases}";
            }
            
            if (isset($session->request->database)) {
                return "Single database: {$session->request->database}";
            }
            
            if (isset($session->asset->databases)) {
                return is_array($session->asset->databases) ? 
                    "Multiple databases: " . implode(', ', $session->asset->databases) : 
                    "Single database: {$session->asset->databases}";
            }
            
            if (isset($session->asset->database)) {
                return "Single database: {$session->asset->database}";
            }
            
            return "All databases (default)";
            
        } catch (\Exception $e) {
            return "All databases (default)";
        }
    }
}
