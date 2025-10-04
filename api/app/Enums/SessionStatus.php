<?php

namespace App\Enums;

enum SessionStatus: string
{
    case SCHEDULED = 'scheduled';
    case CANCELLED = 'cancelled';
    case STARTED = 'started';
    case ENDED = 'ended';
    case EXPIRED = 'expired';
    case TERMINATED = 'terminated';
}
