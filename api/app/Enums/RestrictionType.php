<?php

namespace App\Enums;

enum RestrictionType: string
{
    case IP_ADDRESS = 'ip_address';
    case TIME_WINDOW = 'time_window';
    case LOCATION = 'location';
    case DEVICE = 'device';
}
