<?php

namespace App\Listeners;

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
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RequestCreated $event): void
    {
        // AI evaluation then submit -> notification (HandleRequestSubmitted)
        $request = $event->request;
        $request->getAiEvaluation();
        $request->submit();
    }

    public function failed(RequestCreated $event, \Throwable $exception): void {}
}
