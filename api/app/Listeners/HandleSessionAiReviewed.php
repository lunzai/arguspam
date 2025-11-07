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
use Illuminate\Support\Facades\Notification;

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
        if ($session->isRequiredManualReview()) {
            $session
                ->requester
                ->notify(new SessionReviewRequiredNotifyRequester($session));
            $approvers = $session
                ->asset
                ->getApprovers()
                ->except($session->requester_id);
            Notification::send($approvers, new SessionReviewRequiredNotifyApprover($session));
        } else {
            $session
                ->requester
                ->notify(new SessionReviewOptionalNotifyRequester($session));
            if ($session->approver_id !== $session->requester_id) {
                $session
                    ->approver
                    ->notify(new SessionReviewOptionalNotifyApprover($session));
            }
        }
    }
}
