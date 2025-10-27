<?php

namespace App\Listeners;

use App\Events\SessionTerminated;
use App\Notifications\SessionTerminatedNotifyApprover;
use App\Notifications\SessionTerminatedNotifyRequester;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleSessionTerminated implements ShouldBeEncrypted, ShouldQueue
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
    public function handle(SessionTerminated $event): void
    {
        $session = $event->session;
        $session
            ->requester
            ->notify(new SessionTerminatedNotifyRequester($session));
        if ($session->approver_id !== $session->requester_id) {
            $session
                ->approver
                ->notify(new SessionTerminatedNotifyApprover($session));
        }
    }
}
