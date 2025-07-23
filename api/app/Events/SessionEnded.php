<?php

namespace App\Events;

use App\Models\Session;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionEnded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public array $terminationResults;

    /**
     * Create a new event instance.
     */
    public function __construct(Session $session, array $terminationResults)
    {
        $this->session = $session;
        $this->terminationResults = $terminationResults;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
