<?php

namespace Lunzai\QuerySearch\Tests;

use Lunzai\QuerySearch\QuerySearchServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [QuerySearchServiceProvider::class];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Fixtures/Database/migrations');
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Use SQLite in-memory for tests.
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
