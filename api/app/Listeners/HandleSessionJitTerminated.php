<?php

namespace App\Listeners;

use App\Ai\Agents\SessionReviewAgent;
use App\Enums\AiAgentRole;
use App\Events\SessionJitTerminated;
use App\Services\Ai\TenantAiRuntime;
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
        $runtime = app(TenantAiRuntime::class)->forOrgAndRole(
            (int) $session->org_id,
            AiAgentRole::SessionReview,
        );
        $agent = new SessionReviewAgent($session, $runtime->promptViewConfig);
        $userPrompt = view('prompts.session-review.user', [
            'session' => $session,
        ])->render();

        $response = $agent->prompt(
            $userPrompt,
            provider: $runtime->providerChain,
            model: null,
            timeout: $runtime->timeout,
        );
        $session->applyAiAudit($response->toArray());
    }
}
