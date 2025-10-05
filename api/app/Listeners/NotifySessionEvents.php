<?php

namespace App\Listeners;

use App\Events\SessionEnded;
use App\Events\SessionStarted;
use App\Notifications\SessionStartedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifySessionEvents implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    public function handleSessionStarted(SessionStarted $event): void
    {
        $event->session
            ->requester
            ->notify(
                new SessionStartedNotification($event->session)
            );

        if ($event->session->approver) {
            $event->session
                ->approver
                ->notify(
                    new SessionStartedNotification($event->session)
                );
        }
    }

    /**
     * Handle the event.
     */
    public function handleSessionEnded(SessionEnded $event): void
    {
        // $event->session->requester->notify(
        //     new SessionEndedNotification($event->session)
        // );

        // if ($event->session->approver) {
        //     $event->session->approver->notify(
        //         new SessionEndedNotification($event->session)
        //     );
        // }
    }

    public function subscribe($events): array
    {
        return [
            SessionStarted::class => 'handleSessionStarted',
            SessionEnded::class => 'handleSessionEnded',
        ];
    }
}
