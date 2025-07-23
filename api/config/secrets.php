<?php

return [
    'jit' => [
        'prefix' => 'argus',
        'num_length' => 3,
        'random_length' => 5,
        'username_format' => '{prefix}{number}_{random}',
        'password_length' => 16,
        'password_letters' => true,
        'password_numbers' => true,
        'password_symbols' => true,
        'password_spaces' => false,
    ],
    // 'jit' => [
    //     'username_prefix' => env('PAM_JIT_PREFIX', 'jit_'),
    //     'password_length' => env('PAM_JIT_PASSWORD_LENGTH', 24),
    //     'cleanup_frequency' => env('PAM_CLEANUP_FREQUENCY', 'hourly'), // hourly, daily
    // ],

    // // Session settings
    // 'session' => [
    //     'max_duration_hours' => env('PAM_MAX_SESSION_HOURS', 8),
    //     'warning_before_expiry_minutes' => env('PAM_SESSION_WARNING_MINUTES', 15),
    //     'auto_terminate_expired' => env('PAM_AUTO_TERMINATE_EXPIRED', true),
    // ],

    // // Audit settings
    // 'audit' => [
    //     'retain_days' => env('PAM_AUDIT_RETAIN_DAYS', 90),
    //     'compress_after_days' => env('PAM_AUDIT_COMPRESS_DAYS', 30),
    // ],

    // // Security settings
    // 'security' => [
    //     'require_2fa_for_admin_scope' => env('PAM_REQUIRE_2FA_ADMIN', true),
    //     'max_concurrent_sessions_per_user' => env('PAM_MAX_CONCURRENT_SESSIONS', 3),
    //     'block_after_failed_attempts' => env('PAM_BLOCK_FAILED_ATTEMPTS', 5),
    // ],

    // // Supported database systems
    // 'supported_dbms' => [
    //     'mysql',
    //     'postgresql',
    //     'sqlserver',
    //     'mariadb',
    //     'sqlite', // Limited support
    // ],
];
