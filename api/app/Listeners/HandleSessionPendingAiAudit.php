<?php

namespace App\Listeners;

use App\Events\SessionEnded;
use App\Events\SessionTerminated;
use App\Services\OpenAI\OpenAiService;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleSessionPendingAiAudit implements ShouldBeEncrypted, ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;
    public $backoff = 5;

    /**
     * Create the event listener.
     */
    public function __construct(private OpenAiService $openAiService)
    {
    }

    /**
     * Handle the event.
     */
    public function handle(SessionEnded|SessionTerminated $event): void
    {
        $session = $event->session;
        $session->getAiAudit($this->openAiService);
    }
}
