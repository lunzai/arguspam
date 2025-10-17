<?php

namespace App\Listeners;

use App\Events\SessionEnded;
use App\Notifications\SessionEndedNotifyApprover;
use App\Notifications\SessionEndedNotifyRequester;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleSessionEnded implements ShouldBeEncrypted, ShouldQueue
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
    public function handle(SessionEnded $event): void
    {
        $session = $event->session;
        $session
            ->requester
            ->notify(new SessionEndedNotifyRequester($session));
        if ($session->approver_id !== $session->requester_id) {
            $session
                ->approver
                ->notify(new SessionEndedNotifyApprover($session));
        }
    }
}
