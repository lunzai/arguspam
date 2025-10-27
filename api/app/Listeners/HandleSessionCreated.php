<?php

namespace App\Listeners;

use App\Events\SessionCreated;
use App\Notifications\SessionCreatedNotifyApprover;
use App\Notifications\SessionCreatedNotifyRequester;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleSessionCreated implements ShouldBeEncrypted, ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;
    public $backoff = 5;

    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(SessionCreated $event): void
    {
        $session = $event->session;
        $session
            ->requester
            ->notify(new SessionCreatedNotifyRequester($session));
        if ($session->approver_id !== $session->requester_id) {
            $session
                ->approver
                ->notify(new SessionCreatedNotifyApprover($session));
        }
    }
}
