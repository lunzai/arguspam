<?php

namespace App\Listeners;

use App\Events\SessionStarted;
use App\Notifications\SessionStartedNotifyApprover;
use App\Notifications\SessionStartedNotifyRequester;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleSessionStarted implements ShouldBeEncrypted, ShouldQueue
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
    public function handle(SessionStarted $event): void
    {
        $session = $event->session;
        $session
            ->requester
            ->notify(new SessionStartedNotifyRequester($session));
        if ($session->approver_id !== $session->requester_id) {
            $session
                ->approver
                ->notify(new SessionStartedNotifyApprover($session));
        }
    }
}
