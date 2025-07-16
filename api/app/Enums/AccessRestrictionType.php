<?php

namespace App\Enums;

enum AccessRestrictionType: string
{
    case IP_ADDRESS = 'ip address';
    case TIME_WINDOW = 'time window';
    case COUNTRY = 'country';
}
