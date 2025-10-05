<?php

namespace App\Listeners;

use App\Events\RequestCancelled;
use App\Notifications\RequestCancelledNotifyApprover;
use App\Notifications\RequestCancelledNotifyRequester;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class HandleRequestCancelled implements ShouldBeEncrypted, ShouldQueue
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
    public function handle(RequestCancelled $event): void
    {
        $request = $event->request;
        $request->requester->notify(new RequestCancelledNotifyRequester($request));
        $approvers = $request->asset->getApprovers();
        Notification::send($approvers, new RequestCancelledNotifyApprover($request));
    }

    public function failed(RequestCancelled $event, \Throwable $exception): void
    {
        \Log::error('Failed to send request cancelled notifications', [
            'request_id' => $event->request->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
