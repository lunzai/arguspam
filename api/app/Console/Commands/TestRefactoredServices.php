<?php

namespace App\Console\Commands;

use App\Models\Session;
use App\Services\Jit\Secrets\SecretsManager;
use Illuminate\Console\Command;

class TestRefactoredServices extends Command
{
    protected $signature = 'test:refactored:services {session : The session ID to test with}';

    protected $description = 'Test the refactored services with improved architecture';

    public function handle(SecretsManager $secretsManager): int
    {
        $sessionId = $this->argument('session');

        try {
            // Find the session
            $session = Session::with(['asset', 'request'])->findOrFail($sessionId);

            $this->info("Testing Refactored Services with Session ID: {$sessionId}");
            $this->info("Asset: {$session->asset->name} ({$session->asset->host}:{$session->asset->port})");
            $this->info("DBMS: {$session->asset->dbms->value}");
            $this->info("Scope: {$session->request->scope->value}");
            $this->newLine();

            // Test 1: Validate scope
            $this->line('🔍 Testing scope validation...');
            try {
                $isValid = $secretsManager->validateScope($session->asset, $session->request->scope);
                $this->info('✅ Scope validation: '.($isValid ? 'Valid' : 'Invalid'));
            } catch (\Exception $e) {
                $this->error('❌ Scope validation failed: '.$e->getMessage());
            }

            // Test 2: Get admin credentials
            $this->line('🔑 Testing admin credentials retrieval...');
            try {
                $adminCreds = $secretsManager->getAdminCredentials($session->asset);
                $this->info('✅ Admin credentials retrieved successfully');
                $this->line("   Username: {$adminCreds['username']}");
            } catch (\Exception $e) {
                $this->error('❌ Failed to get admin credentials: '.$e->getMessage());
            }

            // Test 3: Generate credentials
            $this->line('🎲 Testing credential generation...');
            try {
                $credentials = $secretsManager->generateCredentials($session->asset);
                $this->info('✅ Credentials generated successfully');
                $this->line("   Username: {$credentials['username']}");
                $this->line("   Password: {$credentials['password']}");
            } catch (\Exception $e) {
                $this->error('❌ Failed to generate credentials: '.$e->getMessage());
            }

            // Test 4: Create JIT account
            $this->line('👤 Testing JIT account creation...');
            $jitAccount = null;
            try {
                $jitAccount = $secretsManager->createAccount($session);
                $this->info('✅ JIT account created successfully');
                $this->line("   Account ID: {$jitAccount->id}");
                $this->line("   Username: {$jitAccount->username}");
                $this->line("   Password: {$jitAccount->password}");
            } catch (\Exception $e) {
                $this->error('❌ Failed to create JIT account: '.$e->getMessage());
            }

            // Test 5: Terminate JIT account (if created)
            if ($jitAccount) {
                $this->line('🗑️  Testing JIT account termination...');
                try {
                    $secretsManager->terminateAccount($session);
                    $this->info('✅ JIT account terminated successfully');
                    $this->line("   Account ID: {$jitAccount->id}");
                    $this->line("   Username: {$jitAccount->username}");
                } catch (\Exception $e) {
                    $this->error('❌ Failed to terminate JIT account: '.$e->getMessage());
                }
            }

            $this->newLine();
            $this->info('🎉 Refactored services test completed!');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Test failed with exception: '.$e->getMessage());
            return self::FAILURE;
        }
    }
}
