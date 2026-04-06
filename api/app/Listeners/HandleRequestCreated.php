<?php

namespace App\Listeners;

use App\Ai\Agents\AccessRequestEvaluator;
use App\Enums\AiAgentRole;
use App\Events\RequestCreated;
use App\Services\Ai\TenantAiRuntime;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleRequestCreated implements ShouldBeEncrypted, ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;

    public $backoff = 5;

    /**
     * Handle the event.
     */
    public function handle(RequestCreated $event): void
    {
        $request = $event->request;
        $runtime = app(TenantAiRuntime::class)->forOrgAndRole(
            (int) $request->org_id,
            AiAgentRole::AccessRequestEvaluation,
        );
        $config = $runtime->promptViewConfig;

        $agent = new AccessRequestEvaluator($request, $config);
        $userPrompt = view('prompts.new-request.user', [
            'config' => $config,
            'request' => $request,
        ])->render();

        $response = $agent->prompt(
            $userPrompt,
            provider: $runtime->providerChain,
            model: null,
            timeout: $runtime->timeout,
        );
        $request->applyAiEvaluation($response->toArray());
        $request->submit();
    }

    public function failed(RequestCreated $event, \Throwable $exception): void {}
}
