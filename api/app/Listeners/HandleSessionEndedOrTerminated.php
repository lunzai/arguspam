<?php

namespace App\Listeners;

use App\Events\SessionEnded;
use App\Events\SessionTerminated;
use App\Services\Jit\Secrets\SecretsManager;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleSessionEndedOrTerminated implements ShouldBeEncrypted, ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;
    public $backoff = 5;

    /**
     * Create the event listener.
     */
    public function __construct(private SecretsManager $secretsManager)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SessionEnded|SessionTerminated $event): void
    {
        $session = $event->session;
        $session->terminateJitAccount();
    }
}
