<?php

return [

    'default' => env('QUEUE_CONNECTION', 'redis'),

    'connections' => [

        'sync' => [
            'driver' => 'sync',
        ],

        // 'database' => [
        //     'driver' => 'database',
        //     'connection' => env('DB_QUEUE_CONNECTION'),
        //     'table' => env('DB_QUEUE_TABLE', 'jobs'),
        //     'queue' => env('DB_QUEUE', 'default'),
        //     'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
        //     'after_commit' => true,
        // ],

        'redis' => [
            'driver' => 'redis',
            'connection' => env('REDIS_QUEUE_CONNECTION', 'queue'),
            'queue' => env('REDIS_QUEUE', 'default'),
            'retry_after' => (int) env('REDIS_QUEUE_RETRY_AFTER', 120),
            'block_for' => null,
            'after_commit' => true,
        ],

    ],

    'batching' => [
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'job_batches',
    ],

    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
        'database' => env('DB_CONNECTION', 'mysql'),
        'table' => 'failed_jobs',
    ],

];
