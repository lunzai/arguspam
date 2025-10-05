<?php

namespace App\Enums;

enum SessionStatus: string
{
    case SCHEDULED = 'scheduled'; // created, not started
    case CANCELLED = 'cancelled'; // cancelled before start
    case STARTED = 'started'; 
    case ENDED = 'ended';
    case EXPIRED = 'expired'; // did not start before end datetime
    case TERMINATED = 'terminated'; // ended by approver or timeout
}
