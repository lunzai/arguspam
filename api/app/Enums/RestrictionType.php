<?php

namespace App\Enums;

enum RestrictionType: string
{
    case IP = 'ip';
    case LOCATION = 'location';
    case TIME = 'time';
}
