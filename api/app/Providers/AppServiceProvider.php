<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\DashboardPolicy;
use App\Policies\OrgUserPolicy;
use App\Policies\PasswordPolicy;
use App\Policies\RolePermissionPolicy;
use App\Policies\UserGroupUserPolicy;
use App\Policies\UserOrgPolicy;
use App\Policies\UserRolePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\TelescopeServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });
    }
}
