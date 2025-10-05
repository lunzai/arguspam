<?php

namespace App\Listeners;

use App\Events\RequestApproved;
use App\Notifications\RequestApprovedNotifyApprover;
use App\Notifications\RequestApprovedNotifyRequester;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use App\Models\Session;

class HandleRequestApproved implements ShouldBeEncrypted, ShouldQueue
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
    public function handle(RequestApproved $event): void
    {
        $request = $event->request;
        Session::createFromRequest($request);
        $request->requester->notify(new RequestApprovedNotifyRequester($request));
        $approvers = $request->asset->getApprovers();
        Notification::send($approvers, new RequestApprovedNotifyApprover($request));
    }

    public function failed(RequestApproved $event, \Throwable $exception): void
    {
        \Log::error('Failed to send request approved notifications', [
            'request_id' => $event->request->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
