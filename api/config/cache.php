<?php

use Illuminate\Support\Str;

return [

    'default' => env('CACHE_STORE', 'redis'),

    'stores' => [

        // 'array' => [
        //     'driver' => 'array',
        //     'serialize' => false,
        // ],
        // 'file' => [
        //     'driver' => 'file',
        //     'path' => storage_path('framework/cache/data'),
        //     'lock_path' => storage_path('framework/cache/data'),
        // ],
        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
            'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'cache'),
        ],

        'session' => [
            'driver' => 'redis',
            'connection' => 'session',
            'lock_connection' => 'session',
        ],

    ],

    'prefix' => env('CACHE_PREFIX', Str::slug(env('APP_NAME', 'argus'), '_').'_cache_'),

    'default_ttl' => 86400,
];
