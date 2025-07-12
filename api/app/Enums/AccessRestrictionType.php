<?php

namespace App\Enums;

enum AccessRestrictionType: string
{
    case IP_ADDRESS = 'ip_address';
    case TIME_WINDOW = 'time_window';
    case LOCATION = 'location';
}
