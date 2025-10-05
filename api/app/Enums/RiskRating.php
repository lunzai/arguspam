<?php

namespace App\Enums;

use App\Traits\EnumToString;

enum RiskRating: string
{
    use EnumToString;

    case LOW = 'low';
    case MEDIUM = 'medium';
    case HIGH = 'high';
    case CRITICAL = 'critical';

}
