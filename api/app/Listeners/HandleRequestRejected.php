<?php

namespace App\Listeners;

use App\Events\RequestRejected;
use App\Notifications\RequestRejectedNotifyApprover;
use App\Notifications\RequestRejectedNotifyRequester;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class HandleRequestRejected implements ShouldBeEncrypted, ShouldQueue
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
    public function handle(RequestRejected $event): void
    {
        $request = $event->request;
        $request->requester->notify(new RequestRejectedNotifyRequester($request));
        $approvers = $request->asset->getApprovers();
        Notification::send($approvers, new RequestRejectedNotifyApprover($request));
    }

    public function failed(RequestRejected $event, \Throwable $exception): void
    {
        \Log::error('Failed to send request rejected notifications', [
            'request_id' => $event->request->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
