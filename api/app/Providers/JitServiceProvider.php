<?php

namespace App\Providers;

use App\Services\Jit\Databases\DatabaseDriverFactory;
use App\Services\Jit\JitManager;
use Illuminate\Support\ServiceProvider;

class JitServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Service bindings
        $this->app->singleton(DatabaseDriverFactory::class);

        // JitManager with dependencies
        $this->app->singleton(JitManager::class, function ($app) {
            return new JitManager($app->make(DatabaseDriverFactory::class));
        });
    }

    public function boot(): void {}
}
