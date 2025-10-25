<?php

namespace App\Providers;

use App\Services\Jit\Databases\DatabaseDriverFactory;
use App\Services\Jit\Secrets\SecretsManager;
use Illuminate\Support\ServiceProvider;

class JitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Service bindings
        $this->app->singleton(DatabaseDriverFactory::class);

        // SecretsManager with dependencies
        $this->app->singleton(SecretsManager::class, function ($app) {
            return new SecretsManager(
                $app->make(DatabaseDriverFactory::class),
                config('pam.database', [])
            );
        });
    }

    public function boot(): void {}
}
