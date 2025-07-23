<?php

namespace App\Services\Secrets;

use App\Services\Secrets\Console\CleanupExpiredJitAccounts;
use Illuminate\Support\ServiceProvider;

class SecretsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SecretsManager::class, function ($app) {
            return new SecretsManager;
        });
        // if ($this->app->runningInConsole()) {
        //     $this->registerCommands();
        // }
    }

    public function boot(): void
    {
        // $this->publishes([
        //     __DIR__ . '/config/secrets.php' => config_path('secrets.php'),
        // ], 'config');
        // if ($this->app->runningInConsole()) {
        //     $this->commands([
        //         // SecretsCleanupCommand::class,
        //     ]);
        // }
    }

    // protected function registerCommands(): void
    // {
    //     $this->commands([
    //         CleanupExpiredJitAccounts::class,
    //     ]);
    // }
}
