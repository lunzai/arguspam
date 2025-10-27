<?php

namespace App\Events;

use App\Models\Request;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RequestCancelled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    /**
     * Create a new event instance.
     */
    public function __construct(public Request $request) {}
}
