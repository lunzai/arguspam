<?php

namespace App\Jobs;

use App\Models\Session;
use App\Services\Secrets\SecretsManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessExpiredSessions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    public function handle(SecretsManager $secretsManager): void
    {
        // Find expired active sessions
        $expiredSessions = Session::where('status', 'active')
            ->where('scheduled_end_datetime', '<', now())
            ->get();

        foreach ($expiredSessions as $session) {
            try {
                // Terminate JIT account
                $terminationResults = $secretsManager->terminateAccount($session);

                // Update session
                $session->update([
                    'status' => 'expired',
                    'is_expired' => true,
                    'end_datetime' => $session->scheduled_end_datetime,
                    'actual_duration' => $session->scheduled_end_datetime->diffInMinutes($session->start_datetime),
                ]);

                Log::info('Processed expired session', [
                    'session_id' => $session->id,
                    'termination_results' => $terminationResults,
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to process expired session', [
                    'session_id' => $session->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Also clean up any orphaned JIT accounts
        $secretsManager->cleanupExpiredAccounts();
    }
}
