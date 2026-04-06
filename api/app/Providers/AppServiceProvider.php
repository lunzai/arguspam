<?php

namespace App\Providers;

use App\Models\OrgAiProvider;
use App\Models\User;
use App\Observers\OrgAiProviderObserver;
use App\Services\Ai\TenantAiRuntime;
use App\Services\OrgSettingsRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(TenantAiRuntime::class);
        $this->app->singleton(OrgSettingsRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        OrgAiProvider::observe(OrgAiProviderObserver::class);

        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });
    }
}
