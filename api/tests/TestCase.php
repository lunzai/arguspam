<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * Laravel 12's base TestCase already provides a proper implementation
     * that loads bootstrap/app.php and bootstraps the kernel.
     * No need to override unless you have custom bootstrap logic.
     */

    /**
     * Setup the test environment.
     *
     * This runs before each test to ensure we're using the test database.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // CRITICAL SAFETY CHECK: Ensure we're NEVER using the production database
        $this->ensureTestDatabaseIsUsed();
    }

    /**
     * Ensure that tests are using the test database and not production.
     *
     * This is a critical safety check to prevent data loss.
     */
    protected function ensureTestDatabaseIsUsed(): void
    {
        $database = config('database.connections.mysql.database');
        $environment = app()->environment();

        // Ensure we're in testing environment
        if ($environment !== 'testing') {
            throw new \RuntimeException(
                "Tests MUST run in 'testing' environment. Current environment: {$environment}. ".
                'This prevents accidental data loss in production/development databases.'
            );
        }

        // Ensure database name contains 'test'
        if (!str_contains($database, 'test')) {
            throw new \RuntimeException(
                "Database name MUST contain 'test'. Current database: {$database}. ".
                'This is a safety check to prevent wiping production data. '.
                'Check your phpunit.xml DB_DATABASE setting.'
            );
        }

        // Ensure we're not using the production database name
        $productionDbName = 'arguspam'; // Your production database name
        if ($database === $productionDbName) {
            throw new \RuntimeException(
                "DANGER: Tests are configured to use production database '{$productionDbName}'! ".
                'This would WIPE ALL PRODUCTION DATA. Tests have been blocked. '.
                'Fix your phpunit.xml to use a test database (e.g., arguspam_test).'
            );
        }
    }
}
