<?php

namespace App\Console\Commands;

use App\Models\Session;
use App\Services\Secrets\SecretsManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;

class TestAccountCreate extends Command
{
    protected $signature = 'test:account:create {session : The session ID to create JIT account for}';

    protected $description = 'Create a JIT account for a session and output credentials in plaintext';

    public function handle(SecretsManager $secretsManager): int
    {
        $sessionId = $this->argument('session');

        try {
            // Find the session
            $session = Session::with(['asset', 'request'])->findOrFail($sessionId);
            
            $this->info("Creating JIT account for Session ID: {$sessionId}");
            $this->info("Asset: {$session->asset->name} ({$session->asset->host}:{$session->asset->port})");
            $this->info("DBMS: {$session->asset->dbms->value}");
            $this->info("Scope: {$session->request->scope->value}");
            $this->info("Expires at: {$session->scheduled_end_datetime}");
            
            // Show database access configuration
            $this->showDatabaseAccessInfo($session);
            
            // Create the JIT account
            $jitAccount = $secretsManager->createAccount($session);
            
            // Decrypt and display credentials
            $username = $jitAccount->username;
            $password = Crypt::decryptString($jitAccount->password);
            
            $this->newLine();
            $this->info('✅ JIT Account created successfully!');
            $this->newLine();
            $this->line('📋 Account Details:');
            $this->line("   Username: <fg=green>{$username}</>");
            $this->line("   Password: <fg=green>{$password}</>");
            $this->line("   Account ID: {$jitAccount->id}");
            $this->line("   Expires at: {$jitAccount->expires_at}");
            $this->newLine();
            
            return self::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('❌ Failed to create JIT account: ' . $e->getMessage());
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
