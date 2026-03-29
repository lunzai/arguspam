<?php

namespace App\Listeners;

use App\Ai\Agents\AccessRequestEvaluator;
use App\Events\RequestCreated;
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
        $config = array_merge(config('pam.openai', []), config('pam.access_request.duration', []));

        $agent = new AccessRequestEvaluator($request, $config);
        $userPrompt = view('prompts.new-request.user', [
            'config' => $config,
            'request' => $request,
        ])->render();

        $response = $agent->prompt(
            $userPrompt,
            model: $config['model'] ?? 'gpt-4o-mini',
            timeout: 120,
        );
        $request->applyAiEvaluation($response->toArray());
        $request->submit();
    }

    public function failed(RequestCreated $event, \Throwable $exception): void {}
}
