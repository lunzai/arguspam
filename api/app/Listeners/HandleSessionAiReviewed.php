<?php

namespace App\Listeners;

use App\Events\SessionAiAudited;
use App\Notifications\SessionReviewOptionalNotifyApprover;
use App\Notifications\SessionReviewOptionalNotifyRequester;
use App\Notifications\SessionReviewRequiredNotifyApprover;
use App\Notifications\SessionReviewRequiredNotifyRequester;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleSessionAiReviewed implements ShouldBeEncrypted, ShouldQueue
{
    use InteractsWithQueue;

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
    public function handle(SessionAiAudited $event): void
    {
        $session = $event->session;
        $requesterNotification = $session->isRequiredManualReview() ?
            new SessionReviewRequiredNotifyRequester($session) :
            new SessionReviewOptionalNotifyRequester($session);
        $approverNotification = $session->isRequiredManualReview() ?
            new SessionReviewRequiredNotifyApprover($session) :
            new SessionReviewOptionalNotifyApprover($session);
        $session
            ->requester
            ->notify($requesterNotification);
        if ($session->approver_id !== $session->requester_id) {
            $session
                ->approver
                ->notify($approverNotification);
        }
    }
}
