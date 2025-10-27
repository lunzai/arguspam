<?php

namespace App\Constants;

class LogMessages
{
    // Credential Manager
    public const NO_ACTIVE_ADMIN_ACCOUNT = 'No active admin account found';
    public const FAILED_GET_ADMIN_CREDENTIALS = 'Failed to get admin credentials';
    public const GENERATED_CREDENTIALS_VALIDATION_FAILED = 'Generated credentials failed validation';
    public const FAILED_GENERATE_CREDENTIALS = 'Failed to generate credentials';
    public const FAILED_ENCRYPT_PASSWORD = 'Failed to encrypt password';
    public const FAILED_DECRYPT_PASSWORD = 'Failed to decrypt password';

    // JIT Account Manager
    public const CREATING_JIT_ACCOUNT = 'Creating JIT account for session';
    public const JIT_ACCOUNT_VALIDATION_FAILED = 'JIT account validation failed';
    public const FAILED_CREATE_JIT_USER_DB = 'Failed to create JIT user in database';
    public const FAILED_CREATE_JIT_ACCOUNT = 'Failed to create JIT account';
    public const JIT_ACCOUNT_CREATED_SUCCESS = 'JIT account created successfully';
    public const TERMINATING_JIT_ACCOUNT = 'Terminating JIT account for session';
    public const FAILED_TERMINATE_JIT_ACCOUNT = 'Failed to terminate JIT account';
    public const JIT_ACCOUNT_TERMINATED_SUCCESS = 'JIT account terminated successfully';
    public const CLEANED_UP_EXPIRED_JIT_ACCOUNTS = 'Cleaned up expired JIT accounts';

    // Audit Log Manager
    public const DB_DRIVER_NOT_PROVIDED = 'Database driver not provided for query log retrieval';
    public const FAILED_RETRIEVE_QUERY_LOGS = 'Failed to retrieve query logs';
    public const STORED_SESSION_AUDIT_LOGS = 'Stored session audit logs';
    public const FAILED_STORE_AUDIT_LOGS = 'Failed to store audit logs';
    public const FAILED_RETRIEVE_STORED_AUDIT_LOGS = 'Failed to retrieve stored audit logs';

    // Secrets Manager
    public const CREATING_JIT_ACCOUNT_SESSION = 'Creating JIT account for session';
    public const JIT_ACCOUNT_CREATED_SUCCESS_SESSION = 'JIT account created successfully';
    public const FAILED_CREATE_JIT_ACCOUNT_CREDENTIALS = 'Failed to create JIT account - credentials not found';
    public const FAILED_CREATE_JIT_ACCOUNT_DB_CONNECTION = 'Failed to create JIT account - database connection failed';
    public const TERMINATING_JIT_ACCOUNT_SESSION = 'Terminating JIT account for session';
    public const RETRIEVED_STORED_AUDIT_LOGS = 'Retrieved and stored audit logs';
    public const FAILED_RETRIEVE_AUDIT_LOGS_TERMINATION = 'Failed to retrieve audit logs during termination';
    public const JIT_ACCOUNT_TERMINATED_SUCCESS_SESSION = 'JIT account terminated successfully';
    public const FAILED_TERMINATE_JIT_ACCOUNT_CREDENTIALS = 'Failed to terminate JIT account - credentials not found';
    public const FAILED_TERMINATE_JIT_ACCOUNT_DB_CONNECTION = 'Failed to terminate JIT account - database connection failed';
    public const FAILED_CONNECT_DATABASE = 'Failed to connect to database';
    public const FAILED_CREATE_DATABASE_DRIVER = 'Failed to create database driver';
    public const FAILED_VALIDATE_SCOPE_CREDENTIALS = 'Failed to validate scope - credentials not found';
    public const FAILED_VALIDATE_SCOPE_DB_CONNECTION = 'Failed to validate scope - database connection failed';
    public const FAILED_VALIDATE_SCOPE = 'Failed to validate scope';

    // Database Driver
    public const FAILED_TEST_ADMIN_CONNECTION = 'Failed to test admin connection';
    public const DB_OPERATION_FAILED = 'Database operation failed';
}
