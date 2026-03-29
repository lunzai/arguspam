<?php

namespace App\Listeners;

use App\Ai\Agents\SessionReviewAgent;
use App\Events\SessionJitTerminated;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleSessionJitTerminated implements ShouldBeEncrypted, ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;

    public $backoff = 10;

    public function handle(SessionJitTerminated $event): void
    {
        $session = $event->session;
        $agent = new SessionReviewAgent($session);
        $userPrompt = view('prompts.session-review.user', [
            'session' => $session,
        ])->render();

        $config = config('pam.openai', []);
        $response = $agent->prompt(
            $userPrompt,
            model: $config['model'] ?? 'gpt-4o-mini',
            timeout: 120,
        );
        $session->applyAiAudit($response->toArray());
    }
}
