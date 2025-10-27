<?php

namespace App\Listeners;

use App\Events\RequestExpired;
use App\Notifications\RequestExpiredNotification;
use Illuminate\Contracts\Queue\ShouldBeEncrypted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleRequestExpired implements ShouldBeEncrypted, ShouldQueue
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
    public function handle(RequestExpired $event): void
    {
        $request = $event->request;
        $request
            ->requester
            ->notify(new RequestExpiredNotification($request));
    }

    public function failed(RequestExpired $event, \Throwable $exception): void
    {
        \Log::error('Failed to send request expired notifications', [
            'request_id' => $event->request->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
