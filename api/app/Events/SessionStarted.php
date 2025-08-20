<?php

namespace App\Events;

use App\Models\Session;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionStarted
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $session;
    public array $credentials;

    /**
     * Create a new event instance.
     */
    public function __construct(Session $session, array $credentials)
    {
        $this->session = $session;
        $this->credentials = $credentials;
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
