<?php

namespace App\Listeners;

use App\Events\RequestSubmitted;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleRequestSubmitted implements ShouldQueue, ShouldBeEncrypted
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
    public function handle(RequestSubmitted $event): void
    {
        $request = $event->request;
        // notify requester -> requested submitted, approvers notified, pending approval
        // notify approver -> requested submitted, pending approval
    }

    public function failed(RequestSubmitted $event, \Throwable $exception): void
    {
    }
}
