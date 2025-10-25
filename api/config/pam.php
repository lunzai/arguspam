<?php

return [

    'app' => [
        'web_url' => env('APP_WEB_URL', 'http://localhost'),
        'site_url' => 'https://arguspam.com/',
        'github_url' => 'https://github.com/lunzai/arguspam',
    ],

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
        'model' => env('OPENAI_MODEL', 'gpt-4.1-nano'),
        'temperature' => env('OPENAI_TEMPERATURE', 0.2),
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
        'admin_bypass_gate' => env('RBAC_ADMIN_BYPASS_GATE', false),
    ],

    'notification' => [
        'email' => [
            'support' => env('EMAIL_SUPPORT', 'support@arguspam.com'),
        ],
        'slack' => [
            'enabled' => env('SLACK_NOTIFICATION', false),
            'horizon_webhook' => env('SLACK_HORIZON_WEBHOOK'),
            'channel' => [
                'alerts' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
                'requests' => env('SLACK_BOT_USER_REQUEST_CHANNEL'),
                'sessions' => env('SLACK_BOT_USER_SESSION_CHANNEL'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for database operations, JIT accounts, and validation rules.
    | These settings control how database users are created and managed.
    |
    */

    'jit' => [
        'username_prefix' => env('PAM_JIT_USERNAME_PREFIX', 'argus'),
        'username_length' => env('PAM_JIT_USERNAME_LENGTH', 3),
        'random_length' => env('PAM_JIT_RANDOM_LENGTH', 5),
        'username_format' => env('PAM_JIT_USERNAME_FORMAT', '{prefix}{number}_{random}'),
        'generate_password_length' => env('PAM_JIT_PASSWORD_LENGTH', 16),
        'generate_password_letters' => env('PAM_JIT_PASSWORD_LETTERS', true),
        'generate_password_numbers' => env('PAM_JIT_PASSWORD_NUMBERS', true),
        'generate_password_symbols' => env('PAM_JIT_PASSWORD_SYMBOLS', true),
        'generate_password_spaces' => env('PAM_JIT_PASSWORD_SPACES', false),
    ],
    'database' => [

        // Database connection settings
        'connection' => [
            'mysql_port' => env('PAM_MYSQL_PORT', 3306),
            'postgresql_port' => env('PAM_POSTGRESQL_PORT', 5432),
            'mysql_charset' => env('PAM_MYSQL_CHARSET', 'utf8mb4'),
        ],

        // Account expiration
        'expiration' => [
            'default_days' => env('PAM_ACCOUNT_EXPIRY_DAYS', 1),
            'max_days' => env('PAM_MAX_ACCOUNT_EXPIRY_DAYS', 30),
        ],

        // Validation rules
        'validation' => [
            'username' => [
                'min_length' => env('PAM_USERNAME_MIN_LENGTH', 3),
                'max_length' => env('PAM_USERNAME_MAX_LENGTH', 32),
            ],
            'password' => [
                'min_length' => env('PAM_PASSWORD_MIN_LENGTH', 8),
                'max_length' => env('PAM_PASSWORD_MAX_LENGTH', 128),
            ],
        ],

        // Database access patterns
        'access' => [
            'all_databases_indicator' => null,
            'empty_databases_indicator' => [],
        ],
    ],
];
