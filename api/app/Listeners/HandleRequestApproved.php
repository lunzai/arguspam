<?php

namespace App\Listeners;

use App\Events\RequestApproved;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleRequestApproved implements ShouldQueue, ShouldBeEncrypted
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
    public function handle(RequestApproved $event): void
    {
        $request = $event->request;
        // notify requester -> requested approved
        // notify approver -> requested approved
    }

    public function failed(RequestApproved $event, \Throwable $exception): void
    {
    }
}
