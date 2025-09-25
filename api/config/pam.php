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
            'min' => 20,          // 10 minutes minimum
            'max' => 43200,       // 30 days maximum
        ],
        // 'approval_required' => true,
        // 'max_active_requests' => 5,
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
