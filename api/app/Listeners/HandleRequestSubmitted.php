<?php

namespace App\Listeners;

use App\Events\RequestSubmitted;
use App\Notifications\RequestSubmittedNotifyApprover;
use App\Notifications\RequestSubmittedNotifyRequester;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class HandleRequestSubmitted implements ShouldBeEncrypted, ShouldQueue
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
    public function handle(RequestSubmitted $event): void
    {
        $request = $event->request;

        $request
            ->requester
            ->notify(new RequestSubmittedNotifyRequester($request));
        $approvers = $request
            ->asset
            ->getApprovers()
            ->except($request->requester_id);
        Notification::send($approvers, new RequestSubmittedNotifyApprover($request));
        // Notification::route('email', config('pam.notification.email.support'))
        //     ->notify(new RequestSubmittedNotifyApprover($request));
        // Notification::route('slack', config('pam.notification.slack.channel.requests'))
        //     ->notify(new RequestSubmittedNotifyApprover($request));
    }

    public function failed(RequestSubmitted $event, \Throwable $exception): void
    {
        // Log the failure or handle it appropriately
        \Log::error('Failed to send request submitted notifications', [
            'request_id' => $event->request->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
