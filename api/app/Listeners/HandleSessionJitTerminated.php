<?php

namespace App\Listeners;

use App\Events\SessionJitTerminated;
use App\Services\OpenAi\OpenAiService;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleSessionJitTerminated implements ShouldBeEncrypted, ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;
    public $backoff = 10;

    public function __construct(private OpenAiService $openAiService) {}

    public function handle(SessionJitTerminated $event): void
    {
        $session = $event->session;
        $session->getAiAudit($this->openAiService);
    }
}
