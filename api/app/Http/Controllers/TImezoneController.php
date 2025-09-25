<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TimezoneController extends Controller
{
    public function index(Request $request): array
    {
        $tz = Cache::remember(
            CacheKey::TIMEZONES->value,
            config('cache.default_ttl'),
            function () {
                return \DateTimeZone::listIdentifiers();
            }
        );
        return [
            'data' => $tz,
        ];
    }
}
