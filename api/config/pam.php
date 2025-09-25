<?php

return [
    'user' => [
        'default_timezone' => 'Asia/Singapore',
    ],

    /*
    |--------------------------------------------------------------------------
    | Organization
    |--------------------------------------------------------------------------
    |
    | Default organization settings.
    |
    */
    'org' => [
        'request_header' => 'x-organization-id',
        'request_attribute' => 'current_org_id',
    ],

    'auth' => [
        'temp_key_expiration' => env('AUTH_TEMP_KEY_EXPIRATION', 5), // 10 minutes
        'bypass_2fa' => env('AUTH_BYPASS_2FA', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Management
    |--------------------------------------------------------------------------
    |
    | Here you can configure password-related settings such as minimum length,
    | complexity requirements, and expiration policies.
    |
    */
    'password' => [
        'min_length' => 8,
        // 'require_special_chars' => true,
        // 'require_numbers' => true,
        // 'expiry_days' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | Access Request Settings
    |--------------------------------------------------------------------------
    |
    | Configure the duration limits and other settings for access requests.
    | Duration is in minutes.
    |
    */
    'access_request' => [
        'duration' => [
            'min' => 20,          // 20 minutes minimum
            'max' => 43200,       // 30 days maximum
            'recommended_min' => 20, // 20 minutes
            'recommended_max' => 43200, // 30 days
            'low_threshold' => 240, // 4 hours
            'medium_threshold' => 1440, // 24 hours
            'high_threshold' => 10080, // 7 days
        ],
        // 'approval_required' => true,
        // 'max_active_requests' => 5,
    ],

    'openai' => [
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
        'temperature' => env('OPENAI_TEMPERATURE', 0.1),
        'max_output_tokens' => env('OPENAI_MAX_OUTPUT_TOKENS', 2048),
        'top_p' => env('OPENAI_TOP_P', 1),
        'store' => env('OPENAI_STORE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Default pagination settings.
    |
    */
    'pagination' => [
        'per_page' => 20,
    ],

    /*
    |--------------------------------------------------------------------------
    | RBAC
    |--------------------------------------------------------------------------
    |
    | Default RBAC settings.
    |
    */
    'rbac' => [
        'default_admin_role' => 'Admin',
        'default_user_role' => 'User',
    ],
];
