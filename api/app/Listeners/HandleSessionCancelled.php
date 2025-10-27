<?php

namespace App\Listeners;

use App\Events\SessionCancelled;
use App\Notifications\SessionCancelledNotifyApprover;
use App\Notifications\SessionCancelledNotifyRequester;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleSessionCancelled implements ShouldBeEncrypted, ShouldQueue
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
    public function handle(SessionCancelled $event): void
    {
        $session = $event->session;
        $session
            ->requester
            ->notify(new SessionCancelledNotifyRequester($session));
        if ($session->approver_id !== $session->requester_id) {
            $session
                ->approver
                ->notify(new SessionCancelledNotifyApprover($session));
        }
    }
}
