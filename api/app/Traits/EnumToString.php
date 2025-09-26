<?php

namespace App\Traits;

trait EnumToString
{
    public static function toString($delimiter = '|'): string
    {
        return collect(static::cases())
            ->pluck('value')
            ->implode($delimiter);
    }
}
