<?php

namespace App\Listeners;

use App\Events\SessionExpired;
use App\Notifications\SessionExpiredNotifyApprover;
use App\Notifications\SessionExpiredNotifyRequester;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleSessionExpired implements ShouldBeEncrypted, ShouldQueue
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
    public function handle(SessionExpired $event): void
    {
        $session = $event->session;
        $session
            ->requester
            ->notify(new SessionExpiredNotifyRequester($session));
        if ($session->approver_id !== $session->requester_id) {
            $session
                ->approver
                ->notify(new SessionExpiredNotifyApprover($session));
        }
    }
}
