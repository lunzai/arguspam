<?php

namespace App\Listeners;

use App\Events\RequestRejected;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleRequestRejected implements ShouldQueue, ShouldBeEncrypted
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
    public function handle(RequestRejected $event): void
    {
        $request = $event->request;
        // notify requester -> requested rejected
        // notify approver -> requested rejected
    }

    public function failed(RequestRejected $event, \Throwable $exception): void
    {
    }
}
